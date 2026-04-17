<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    use HasFactory;

    protected $table = 'comm_recipients';

    protected $fillable = [
        'comm_campaign_id',
        'user_id',
        'status',
        'sent_at',
        'error_message',
        'is_read',
        'read_at',
        // UIMMS fields
        'is_active_participant',
        'assigned_at',
        'last_activity_at',
        'participation_history',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        // UIMMS casts
        'is_active_participant' => 'boolean',
        'assigned_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'participation_history' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'comm_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // UIMMS Methods
    public function updateActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function addToParticipationHistory($action, $details = null)
    {
        $history = $this->participation_history ?? [];
        $history[] = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['participation_history' => $history]);
    }

    public function scopeActiveParticipants($query)
    {
        return $query->where('is_active_participant', true);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_active_participant', true);
    }
}