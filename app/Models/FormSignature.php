<?php

namespace App\Models;

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
}
