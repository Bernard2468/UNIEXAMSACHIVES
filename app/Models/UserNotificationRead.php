<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'system_notification_id',
        'read_at',
        'dismissed_at',
        'acknowledged_at',
        'device_type',
        'ip_address'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function systemNotification(): BelongsTo
    {
        return $this->belongsTo(SystemNotification::class);
    }

    // Scopes
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeDismissed($query)
    {
        return $query->whereNotNull('dismissed_at');
    }

    public function scopeAcknowledged($query)
    {
        return $query->whereNotNull('acknowledged_at');
    }

    // Accessors
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getIsDismissedAttribute(): bool
    {
        return !is_null($this->dismissed_at);
    }

    public function getIsAcknowledgedAttribute(): bool
    {
        return !is_null($this->acknowledged_at);
    }
}

