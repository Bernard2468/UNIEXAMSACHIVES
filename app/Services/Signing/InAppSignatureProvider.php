<?php

namespace App\Services\Signing;

use App\Forms\Contracts\SignatureProvider;
use App\Models\FormSignature;
use App\Models\FormSubmission;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Default signature provider. Captures a base64 PNG signature drawn in the
 * browser, persists it to the `public` disk, and writes a FormSignature row
 * including the tamper-evident hash chain.
 *
 * Context shape:
 *   [
 *     'signature_data' => 'data:image/png;base64,iVBORw0...',  // required (unless reuse_saved=true)
 *     'reuse_saved'    => bool,                                 // optional — use the user's saved signature
 *     'ip'             => string|null,
 *     'user_agent'     => string|null,
 *   ]
 */
class InAppSignatureProvider implements SignatureProvider
{
    public function name(): string
    {
        return 'in_app';
    }

    public function sign(
        FormSubmission $submission,
        string $stageSlug,
        User $signer,
        array $payload,
        array $context = []
    ): FormSignature {
        // Pin to whole-second precision. The chain hash is computed from this
        // ISO string, and the `signed_at` column stores at precision 0 — if we
        // hashed the microsecond-bearing now(), verifyChain() would reread a
        // truncated timestamp and produce a different hash on every row.
        $signedAt = now()->startOfSecond();
        $signedAtIso = $signedAt->toISOString();

        $priorHash = FormSignature::where('form_submission_id', $submission->id)
            ->orderByDesc('signed_at')
            ->orderByDesc('id')
            ->value('chain_hash');

        $payloadHash = SignatureService::payloadHash($payload);
        $chainHash   = SignatureService::chainHash($priorHash, $payloadHash, $signer->id, $signedAtIso);

        $signatureImagePath = $this->persistSignatureImage($submission, $stageSlug, $signer, $context);

        // Optionally save this signature as the user's reusable signature.
        // We only do this when the user supplied a NEW signature (drawn or
        // typed) — there's nothing to save if they ticked "reuse saved".
        if (!empty($context['save_as_my_signature'])
            && empty($context['reuse_saved'])
            && !empty($context['signature_data'])) {
            $this->persistAsUserSignature($signer, $context['signature_data']);
        }

        return FormSignature::create([
            'form_submission_id'   => $submission->id,
            'stage_slug'           => $stageSlug,
            'user_id'              => $signer->id,
            'signature_image_path' => $signatureImagePath,
            'signature_image_data' => null,
            'signed_at'            => $signedAt,
            'ip_address'           => $context['ip'] ?? null,
            'user_agent'           => $context['user_agent'] ?? null,
            'prior_hash'           => $priorHash,
            'payload_hash'         => $payloadHash,
            'chain_hash'           => $chainHash,
            'provider'             => $this->name(),
            'provider_envelope_id' => null,
        ]);
    }

    /**
     * Decode the base64 PNG (or reuse the user's saved signature) and persist
     * it on the public disk under `form-signatures/{submission_id}/`.
     */
    protected function persistSignatureImage(
        FormSubmission $submission,
        string $stageSlug,
        User $signer,
        array $context
    ): ?string {
        if (!empty($context['reuse_saved'])) {
            $saved = $signer->savedSignature;
            if ($saved && $saved->signature_image_path) {
                return $saved->signature_image_path;
            }
        }

        $data = $context['signature_data'] ?? null;
        if (!$data) {
            return null;
        }

        if (str_starts_with($data, 'data:')) {
            $commaPos = strpos($data, ',');
            $data = $commaPos !== false ? substr($data, $commaPos + 1) : '';
        }

        $binary = base64_decode($data, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        $directory = 'form-signatures/' . $submission->id;
        $filename  = $stageSlug . '-' . $signer->id . '-' . Str::random(8) . '.png';
        $path      = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Copy the freshly-captured signature into the user's reusable saved
     * signature slot. The file is written to a SEPARATE path under
     * user-signatures/ so the saved copy is independent of form-signature
     * file lifecycle — deleting the form will never break the saved sig.
     *
     * Tolerates failure: signing must not be aborted if the save fails.
     */
    protected function persistAsUserSignature(User $signer, string $signatureData): void
    {
        try {
            $raw = $signatureData;
            if (str_starts_with($raw, 'data:')) {
                $commaPos = strpos($raw, ',');
                $raw = $commaPos !== false ? substr($raw, $commaPos + 1) : '';
            }

            $binary = base64_decode($raw, true);
            if ($binary === false || $binary === '') {
                return;
            }

            $disk = Storage::disk('public');

            // Replace any existing saved-signature file so we don't accumulate
            // orphan PNGs in user-signatures/ every time someone re-saves.
            $existing = $signer->savedSignature;
            if ($existing && $existing->signature_image_path && $disk->exists($existing->signature_image_path)) {
                $disk->delete($existing->signature_image_path);
            }

            $path = 'user-signatures/' . $signer->id . '-' . Str::random(8) . '.png';
            $disk->put($path, $binary);

            UserSignature::updateOrCreate(
                ['user_id' => $signer->id],
                ['signature_image_path' => $path],
            );
        } catch (\Throwable $e) {
            // Saving the reusable signature is a nice-to-have. The signing
            // itself has already succeeded — log and move on.
            Log::warning('Failed to persist user signature during form sign', [
                'user_id' => $signer->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
