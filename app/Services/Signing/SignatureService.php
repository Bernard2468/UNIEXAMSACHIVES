<?php

namespace App\Services\Signing;

use App\Forms\Contracts\SignatureProvider;
use App\Models\FormSignature;
use App\Models\FormSubmission;
use App\Models\User;

/**
 * Public-facing entry point for form signing.
 *
 * Controllers call this; it delegates to whatever SignatureProvider is bound
 * in the container (in_app by default; DocuSign / PandaDoc / SignNow can be
 * swapped in later without touching the controller).
 */
class SignatureService
{
    public function __construct(protected SignatureProvider $provider)
    {
    }

    public function sign(
        FormSubmission $submission,
        string $stageSlug,
        User $signer,
        array $payload,
        array $context = []
    ): FormSignature {
        return $this->provider->sign($submission, $stageSlug, $signer, $payload, $context);
    }

    /**
     * Canonical JSON representation of a payload — sorts keys recursively so
     * two equivalent payloads always produce the same hash regardless of key
     * ordering or whitespace.
     */
    public static function canonicalize(array $payload): string
    {
        ksort($payload);
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = self::sortRecursive($value);
            }
        }
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected static function sortRecursive(array $value): array
    {
        if (array_is_list($value)) {
            // For lists we preserve order (it is meaningful) but still
            // canonicalize any nested associative arrays.
            return array_map(
                fn ($v) => is_array($v) ? self::sortRecursive($v) : $v,
                $value
            );
        }

        ksort($value);
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = self::sortRecursive($v);
            }
        }
        return $value;
    }

    /**
     * SHA-256 hex digest of a canonicalised payload.
     */
    public static function payloadHash(array $payload): string
    {
        return hash('sha256', self::canonicalize($payload));
    }

    /**
     * Combine the prior signature's chain hash with the new payload + signer
     * + timestamp to produce this signature's chain hash. If any prior section
     * is tampered with, future verifications will not reproduce.
     */
    public static function chainHash(?string $priorHash, string $payloadHash, int $userId, string $signedAtIso): string
    {
        $material = ($priorHash ?? '') . '|' . $payloadHash . '|' . $userId . '|' . $signedAtIso;
        return hash('sha256', $material);
    }
}
