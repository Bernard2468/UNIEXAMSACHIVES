<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotMessage extends Model
{
    protected $table = 'bot_messages';

    protected $fillable = ['conversation_id', 'role', 'content', 'source'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(BotConversation::class, 'conversation_id');
    }
}
