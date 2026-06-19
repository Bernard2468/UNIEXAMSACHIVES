<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoReply extends Model
{
    use HasFactory;

    /**
     * Invisible structure markers embedded in system-generated chat notes.
     *
     * They are HTML comments, so they render as nothing in the on-screen chat,
     * but let the PDF/print pipeline (see HomeController@exportMemoPdf) tell a
     * system ACTION (forwarded / assigned / created / status change) from a
     * person's own words, and split an action's optional attached comment out
     * so it can be rendered as a distinct "Comment" minute.
     */
    public const PDF_ACTION_MARKER = '<!--cug:action-->';
    public const PDF_REMARK_MARKER = '<!--cug:remark-->';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'message',
        'attachments',
        'is_read',
        'read_at',
        'reply_mode',
        'specific_recipients',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'specific_recipients' => 'array',
    ];

    /**
     * Get the campaign that this reply belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Get the user who made this reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get unread replies
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get replies for a specific campaign
     */
    public function scopeForCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Mark reply as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
