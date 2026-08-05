<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A stored conversation.
 *
 * Two flavours share this table (distinguished by `mode`):
 *   - 'bot'     — an optional MetaGuide transcript (only written when the Super
 *                 Admin has turned on bot_store_transcripts).
 *   - 'support' — a human handoff thread. ALWAYS persisted, because a person has
 *                 to be able to read and answer it.
 */
class BotConversation extends Model
{
    protected $table = 'bot_conversations';

    public const MODE_BOT     = 'bot';
    public const MODE_SUPPORT = 'support';

    public const STATUS_QUEUED   = 'queued';   // waiting for an agent to pick it up
    public const STATUS_ACTIVE   = 'active';   // an agent has replied / claimed it
    public const STATUS_RESOLVED = 'resolved'; // closed (either side can reopen)

    protected $fillable = [
        'user_id', 'page', 'mode', 'status', 'subject', 'category',
        'assigned_agent_id', 'office_id', 'last_user_message_at', 'last_agent_message_at',
        'resolved_at', 'resolved_by', 'user_unread', 'agent_unread', 'meta', 'user_deleted_at',
    ];

    protected $casts = [
        'last_user_message_at'  => 'datetime',
        'last_agent_message_at' => 'datetime',
        'resolved_at'           => 'datetime',
        'user_deleted_at'       => 'datetime',
        'user_unread'           => 'integer',
        'agent_unread'          => 'integer',
        'meta'                  => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(BotMessage::class, 'conversation_id');
    }

    /** The most recent user-visible message — efficient inbox snippets. */
    public function latestMessage()
    {
        return $this->hasOne(BotMessage::class, 'conversation_id')
            ->where('is_internal', false)
            ->latestOfMany();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    // ===== Scopes =====

    public function scopeSupport($query)
    {
        return $query->where('mode', self::MODE_SUPPORT);
    }

    /** Open = still needs attention (queued or active), i.e. not resolved. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_QUEUED, self::STATUS_ACTIVE]);
    }

    /** Not hidden by the user (their history/active views). Agents ignore this. */
    public function scopeVisibleToUser($query)
    {
        return $query->whereNull('user_deleted_at');
    }

    // ===== Helpers =====

    public function isSupport(): bool
    {
        return $this->mode === self::MODE_SUPPORT;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_ACTIVE], true);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
