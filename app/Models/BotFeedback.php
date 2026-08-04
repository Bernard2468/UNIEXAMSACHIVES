<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotFeedback extends Model
{
    protected $table = 'bot_feedback';

    protected $fillable = [
        'user_id', 'rating', 'source', 'matched_key', 'question', 'answer',
    ];
}
