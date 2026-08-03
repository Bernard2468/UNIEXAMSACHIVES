<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An official, signed minute on a memo — created by the "Minute-To" action.
 * Carries a per-minute snapshot of the signer's signature image so the
 * historical record survives the user replacing or deleting their saved
 * signature later.
 */
class MemoMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'minute_no',
        'to_user_ids',
        'to_names',
        'remark',
        'signature_image_path',
        'signed_at',
    ];

    protected $casts = [
        'to_user_ids' => 'array',
        'signed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
