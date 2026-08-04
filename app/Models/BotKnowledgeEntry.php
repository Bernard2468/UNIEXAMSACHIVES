<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Super-Admin-editable knowledge entry, merged on top of the built-in system map.
 */
class BotKnowledgeEntry extends Model
{
    protected $table = 'bot_knowledge_entries';

    protected $fillable = [
        'category', 'title', 'keywords', 'answer', 'links',
        'priority', 'is_active', 'hits',
    ];

    protected $casts = [
        'links'     => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
