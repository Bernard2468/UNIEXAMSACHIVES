<?php

namespace App\Models;

use App\Services\Signing\SignatureService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'stage_slug',
        'user_id',
        'signature_image_path',
        'signature_image_data',
        'signed_at',
        'ip_address',
        'user_agent',
        'prior_hash',
        'payload_hash',
        'chain_hash',
        'provider',
        'provider_envelope_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL the browser can use to render the signature image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->signature_image_path) {
            return asset('storage/' . ltrim($this->signature_image_path, '/'));
        }

        return null;
    }

    /**
     * Re-derive the chain hash from the currently-stored section data and
     * compare against the saved chain_hash. Returns false if either the
     * stage's section data or any prior signature in the chain has been
     * altered after this row was written.
     *
     * Reasons returned (for debugging / forensic display):
     *   - 'payload_mismatch' — section_data for this stage was edited
     *   - 'chain_mismatch'   — prior_hash, user_id or signed_at no longer matches
     */
    public function verifyChain(): array
    {
        $submission = $this->relationLoaded('submission')
            ? $this->submission
            : $this->submission()->first();

        if (!$submission) {
            return ['valid' => false, 'reason' => 'submission_missing'];
        }

        $payload     = $submission->sectionData($this->stage_slug);
        $payloadHash = SignatureService::payloadHash($payload);

        if ($payloadHash !== $this->payload_hash) {
            return ['valid' => false, 'reason' => 'payload_mismatch'];
        }

        $expected = SignatureService::chainHash(
            $this->prior_hash,
            $payloadHash,
            (int) $this->user_id,
            optional($this->signed_at)->toISOString() ?? ''
        );

        if ($expected !== $this->chain_hash) {
            return ['valid' => false, 'reason' => 'chain_mismatch'];
        }

        return ['valid' => true, 'reason' => null];
    }

    public function isChainValid(): bool
    {
        return $this->verifyChain()['valid'];
    }
}
