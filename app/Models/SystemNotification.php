<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SystemNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'notification_type',
        'target_audience',
        'custom_user_ids',
        'subscription_id',
        'priority',
        'requires_acknowledgment',
        'title',
        'message',
        'short_message',
        'icon',
        'color',
        'action_buttons',
        'display_as_banner',
        'display_in_notification_center',
        'send_email',
        'is_dismissible',
        'display_from',
        'display_until',
        'is_active',
        'total_recipients',
        'read_count',
        'dismissed_count',
        'acknowledged_count',
        'email_sent_count',
        'email_sent',
        'email_sent_at',
        'email_template',
        'related_maintenance_id',
        'related_transaction_id',
        'created_by',
        'is_automated',
        'metadata'
    ];

    protected $casts = [
        'custom_user_ids' => 'array',
        'action_buttons' => 'array',
        'display_as_banner' => 'boolean',
        'display_in_notification_center' => 'boolean',
        'send_email' => 'boolean',
        'is_dismissible' => 'boolean',
        'is_active' => 'boolean',
        'requires_acknowledgment' => 'boolean',
        'email_sent' => 'boolean',
        'is_automated' => 'boolean',
        'display_from' => 'datetime',
        'display_until' => 'datetime',
        'email_sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SystemSubscription::class, 'subscription_id');
    }

    public function maintenanceLog(): BelongsTo
    {
        return $this->belongsTo(SystemMaintenanceLog::class, 'related_maintenance_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'related_transaction_id');
    }

    public function userReads(): HasMany
    {
        return $this->hasMany(UserNotificationRead::class, 'system_notification_id');
    }

    public function readByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_notification_reads', 'system_notification_id', 'user_id')
                    ->withPivot(['read_at', 'dismissed_at', 'acknowledged_at'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('display_from')
                           ->orWhere('display_from', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('display_until')
                           ->orWhere('display_until', '>=', now());
                     });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('target_audience', 'all_users')
              ->orWhere('target_audience', 'admins_only')
              ->orWhere('target_audience', 'super_admins_only')
              ->orWhere(function($subQuery) use ($userId) {
                  $subQuery->where('target_audience', 'specific_users')
                           ->whereJsonContains('custom_user_ids', $userId);
              });
        });
    }

    public function scopeUnreadByUser($query, int $userId)
    {
        return $query->whereDoesntHave('userReads', function($q) use ($userId) {
            $q->where('user_id', $userId)->whereNotNull('read_at');
        });
    }

    public function scopeBanners($query)
    {
        return $query->where('display_as_banner', true);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    // Accessors
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary'
        };
    }

    public function getReadPercentageAttribute(): float
    {
        if ($this->total_recipients == 0) {
            return 0;
        }
        
        return round(($this->read_count / $this->total_recipients) * 100, 2);
    }

    public function getDismissedPercentageAttribute(): float
    {
        if ($this->total_recipients == 0) {
            return 0;
        }
        
        return round(($this->dismissed_count / $this->total_recipients) * 100, 2);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->display_until && $this->display_until->isPast();
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->display_from && $this->display_from->isFuture();
    }

    // Business Logic Methods
    public function markAsReadByUser(int $userId): void
    {
        UserNotificationRead::updateOrCreate(
            [
                'user_id' => $userId,
                'system_notification_id' => $this->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        $this->increment('read_count');
    }

    public function markAsDismissedByUser(int $userId): void
    {
        UserNotificationRead::updateOrCreate(
            [
                'user_id' => $userId,
                'system_notification_id' => $this->id,
            ],
            [
                'dismissed_at' => now(),
            ]
        );

        $this->increment('dismissed_count');
    }

    public function markAsAcknowledgedByUser(int $userId): void
    {
        UserNotificationRead::updateOrCreate(
            [
                'user_id' => $userId,
                'system_notification_id' => $this->id,
            ],
            [
                'acknowledged_at' => now(),
                'read_at' => now(),
            ]
        );

        $this->increment('acknowledged_count');
        $this->increment('read_count');
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function setTotalRecipients(int $count): void
    {
        $this->update(['total_recipients' => $count]);
    }

    public function markEmailAsSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
        ]);
    }

    public function incrementEmailSentCount(int $count = 1): void
    {
        $this->increment('email_sent_count', $count);
    }

    public function getTargetedUserIds(): array
    {
        return match($this->target_audience) {
            'all_users' => User::pluck('id')->toArray(),
            'admins_only' => User::where('role', 'admin')->pluck('id')->toArray(),
            'super_admins_only' => User::where('role', 'super_admin')->pluck('id')->toArray(),
            'specific_users' => $this->custom_user_ids ?? [],
            default => []
        };
    }
}

