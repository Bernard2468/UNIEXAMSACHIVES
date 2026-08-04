<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A stored conversation — only ever created when bot_store_transcripts is ON.
 */
class BotConversation extends Model
{
    protected $table = 'bot_conversations';

    protected $fillable = ['user_id', 'page'];

    public function messages(): HasMany
    {
        return $this->hasMany(BotMessage::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
