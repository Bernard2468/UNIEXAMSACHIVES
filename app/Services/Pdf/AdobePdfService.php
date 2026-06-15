<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Thin REST client for Adobe PDF Services (OAuth Server-to-Server).
 *
 * Used by the memo export to turn Word / Excel / PowerPoint attachments into PDF
 * (`createpdf`) and merge them with the generated memo PDF (`combinepdf`). When the
 * credentials are absent the service reports `enabled() === false` and the caller
 * falls back to listing attachments by name only — no network calls are made.
 *
 * Docs: https://developer.adobe.com/document-services/docs/overview/pdf-services-api/
 */
class AdobePdfService
{
    /** Base host for both auth and operations. */
    private const BASE = 'https://pdf-services.adobe.io';

    /** Per-HTTP-call timeout (seconds). */
    private const TIMEOUT = 60;

    /** Max status polls and the gap between them (seconds). ~40s ceiling per job. */
    private const POLL_ATTEMPTS = 40;
    private const POLL_INTERVAL = 1;

    private ?string $clientId;
    private ?string $clientSecret;

    public function __construct()
    {
        $this->clientId     = config('services.adobe_pdf.client_id');
        $this->clientSecret = config('services.adobe_pdf.client_secret');
    }

    /** True only when both credentials are configured. */
    public function enabled(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    /**
     * Return a local filesystem path to a PDF representation of $filePath.
     *
     *  - Already a PDF        → returns $filePath unchanged (no transaction spent).
     *  - Word/Excel/PPT/csv   → converts via Adobe and caches the result by content
     *                           hash under storage/app/adobe-cache, so re-exporting
     *                           the same file never spends a second transaction.
     *  - Anything else / any  → returns null; caller should fall back to a listing.
     *    failure
     */
    public function ensurePdf(string $filePath, string $mime, string $originalName): ?string
    {
        if (! is_file($filePath)) {
            return null;
        }

        $mediaType = $this->mediaTypeFor($originalName, $mime);

        if ($mediaType === 'application/pdf') {
            return $filePath;
        }

        // Only types Adobe createpdf can ingest are worth a round trip.
        if (! $this->isConvertible($mediaType)) {
            return null;
        }

        try {
            $hash      = hash_file('sha256', $filePath);
            $cacheRel  = "adobe-cache/{$hash}.pdf";
            $cacheDisk = Storage::disk('local');

            if ($cacheDisk->exists($cacheRel)) {
                return $cacheDisk->path($cacheRel);
            }

            $assetId     = $this->uploadAsset($filePath, $mediaType);
            $downloadUri = $this->runJob('/operation/createpdf', ['assetID' => $assetId]);
            $pdfBytes    = $this->download($downloadUri);

            $cacheDisk->put($cacheRel, $pdfBytes);

            return $cacheDisk->path($cacheRel);
        } catch (\Throwable $e) {
            Log::warning('AdobePdfService: convert failed for ' . $originalName . ' — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Merge the given PDF files (in order) into a single PDF and return its bytes.
     * Requires at least two inputs. Throws on failure (caller decides the fallback).
     */
    public function combine(array $pdfPaths): string
    {
        $pdfPaths = array_values(array_filter($pdfPaths, 'is_file'));

        if (count($pdfPaths) < 2) {
            throw new \RuntimeException('combine() needs at least two PDF files.');
        }

        $assets = [];
        foreach ($pdfPaths as $path) {
            $assets[] = ['assetID' => $this->uploadAsset($path, 'application/pdf')];
        }

        $downloadUri = $this->runJob('/operation/combinepdf', ['assets' => $assets]);

        return $this->download($downloadUri);
    }

    // ───────────────────────── internals ─────────────────────────

    /** Cached OAuth access token (Adobe tokens last ~24h; we cache for 23h). */
    private function token(): string
    {
        return Cache::remember('adobe_pdf.access_token', now()->addHours(23), function () {
            $resp = Http::asForm()
                ->timeout(self::TIMEOUT)
                ->post(self::BASE . '/token', [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ])
                ->throw();

            $token = $resp->json('access_token');
            if (! $token) {
                throw new \RuntimeException('Adobe token endpoint returned no access_token.');
            }

            return $token;
        });
    }

    /** Authenticated JSON client for the operation/asset endpoints. */
    private function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token(),
            'X-API-Key'     => $this->clientId,
        ])->timeout(self::TIMEOUT);
    }

    /**
     * Reserve an asset slot, upload the file bytes to the presigned URI, and return
     * the assetID for use in an operation body.
     */
    private function uploadAsset(string $filePath, string $mediaType): string
    {
        $created = $this->client()
            ->asJson()
            ->post(self::BASE . '/assets', ['mediaType' => $mediaType])
            ->throw()
            ->json();

        $uploadUri = $created['uploadUri'] ?? null;
        $assetId   = $created['assetID'] ?? null;
        if (! $uploadUri || ! $assetId) {
            throw new \RuntimeException('Adobe /assets returned no uploadUri/assetID.');
        }

        Http::withHeaders(['Content-Type' => $mediaType])
            ->timeout(self::TIMEOUT)
            ->withBody(file_get_contents($filePath), $mediaType)
            ->put($uploadUri)
            ->throw();

        return $assetId;
    }

    /**
     * POST an operation, follow its `location` status URL until the job is done,
     * and return the presigned download URI of the result asset.
     */
    private function runJob(string $endpoint, array $body): string
    {
        $resp = $this->client()->asJson()->post(self::BASE . $endpoint, $body)->throw();

        $statusUrl = $resp->header('location');
        if (! $statusUrl) {
            throw new \RuntimeException("Adobe {$endpoint} returned no location header.");
        }

        for ($i = 0; $i < self::POLL_ATTEMPTS; $i++) {
            $status = $this->client()->get($statusUrl)->throw()->json();
            $state  = $status['status'] ?? null;

            if ($state === 'done') {
                $uri = $status['asset']['downloadUri'] ?? $status['downloadUri'] ?? null;
                if (! $uri) {
                    throw new \RuntimeException("Adobe {$endpoint} finished without a downloadUri.");
                }
                return $uri;
            }

            if ($state === 'failed') {
                throw new \RuntimeException("Adobe {$endpoint} job failed: " . json_encode($status));
            }

            sleep(self::POLL_INTERVAL);
        }

        throw new \RuntimeException("Adobe {$endpoint} job timed out after " . self::POLL_ATTEMPTS . 's.');
    }

    /** Download the result bytes from a presigned URI (no auth headers). */
    private function download(string $uri): string
    {
        return Http::timeout(self::TIMEOUT)->get($uri)->throw()->body();
    }

    /** Map a filename/mime to the Adobe source mediaType, preferring the extension. */
    private function mediaTypeFor(string $name, string $mime): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'  => 'text/csv',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'rtf'  => 'application/rtf',
            'txt'  => 'text/plain',
        ][$ext] ?? ($mime ?: 'application/octet-stream');
    }

    /** Whether Adobe createpdf accepts this source mediaType. */
    private function isConvertible(string $mediaType): bool
    {
        return in_array($mediaType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/rtf',
            'text/plain',
        ], true);
    }
}
