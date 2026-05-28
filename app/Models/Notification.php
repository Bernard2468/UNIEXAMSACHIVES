<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public const CATEGORY_FORM   = 'form';
    public const CATEGORY_MEMO   = 'memo';
    public const CATEGORY_REPLY  = 'reply';
    public const CATEGORY_SYSTEM = 'system';

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'category',
        'title',
        'message',
        'url',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The user whose action triggered this notification (for avatars). */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Derive a category from the type if the column wasn't populated
     * (e.g. legacy rows created before the column existed).
     */
    public function getResolvedCategoryAttribute(): string
    {
        if ($this->category) {
            return $this->category;
        }
        return match (true) {
            str_starts_with((string) $this->type, 'form_') => self::CATEGORY_FORM,
            $this->type === 'reply'                        => self::CATEGORY_REPLY,
            $this->type === 'memo'                         => self::CATEGORY_MEMO,
            default                                        => self::CATEGORY_SYSTEM,
        };
    }

    public static function createMemoReplyNotification($memoCreatorId, $replyAuthor, $memoSubject, $replyUrl, ?int $actorId = null)
    {
        return self::create([
            'user_id'  => $memoCreatorId,
            'actor_id' => $actorId,
            'type'     => 'reply',
            'category' => self::CATEGORY_REPLY,
            'title'    => 'New Reply to Your Memo',
            'message'  => "{$replyAuthor} replied to your memo: \"{$memoSubject}\"",
            'url'      => $replyUrl,
            'data'     => [
                'reply_author' => $replyAuthor,
                'memo_subject' => $memoSubject,
            ],
        ]);
    }
}
