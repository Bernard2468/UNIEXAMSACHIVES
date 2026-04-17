<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks when a user last "read" a memo (opened the chat).
 * Used to split pending memos into Active Chat (unread or new activity) vs Read.
 */
class MemoUserRead extends Model
{
    protected $table = 'memo_user_reads';

    protected $fillable = [
        'user_id',
        'campaign_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}
