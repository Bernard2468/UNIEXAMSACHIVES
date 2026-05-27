<?php

namespace App\Services\Signing;

use App\Forms\Contracts\SignatureProvider;
use App\Models\FormSignature;
use App\Models\FormSubmission;
use App\Models\User;
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
        $signedAt = now();
        $signedAtIso = $signedAt->toISOString();

        $priorHash = FormSignature::where('form_submission_id', $submission->id)
            ->orderByDesc('signed_at')
            ->orderByDesc('id')
            ->value('chain_hash');

        $payloadHash = SignatureService::payloadHash($payload);
        $chainHash   = SignatureService::chainHash($priorHash, $payloadHash, $signer->id, $signedAtIso);

        $signatureImagePath = $this->persistSignatureImage($submission, $stageSlug, $signer, $context);

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
}
