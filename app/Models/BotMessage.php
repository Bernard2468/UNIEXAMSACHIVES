<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotMessage extends Model
{
    protected $table = 'bot_messages';

    public const SENDER_USER   = 'user';
    public const SENDER_AGENT  = 'agent';
    public const SENDER_BOT    = 'bot';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'conversation_id', 'role', 'content', 'source',
        'sender_type', 'sender_id', 'client_id', 'is_internal', 'read_at',
        'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'read_at'     => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(BotConversation::class, 'conversation_id');
    }

    /** The person who sent this (user or agent). Null for bot/system lines. */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
