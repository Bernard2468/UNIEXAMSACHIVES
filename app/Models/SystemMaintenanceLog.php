<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemMaintenanceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_type',
        'title',
        'description',
        'technical_details',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'impact_level',
        'affected_services',
        'requires_downtime',
        'estimated_downtime_minutes',
        'actual_downtime_minutes',
        'notification_sent_to_users',
        'notification_sent_at',
        'users_notified_count',
        'completion_notification_sent',
        'completion_notification_sent_at',
        'display_banner',
        'banner_display_from',
        'banner_display_until',
        'banner_message',
        'performed_by',
        'team_members',
        'completion_notes',
        'issues_encountered',
        'rollback_available',
        'rollback_procedure',
        'rolled_back_at',
        'rolled_back_by',
        'rollback_reason',
        'approved_by',
        'approved_at',
        'metadata'
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'notification_sent_at' => 'datetime',
        'completion_notification_sent_at' => 'datetime',
        'banner_display_from' => 'datetime',
        'banner_display_until' => 'datetime',
        'rolled_back_at' => 'datetime',
        'approved_at' => 'datetime',
        'affected_services' => 'array',
        'team_members' => 'array',
        'metadata' => 'array',
        'requires_downtime' => 'boolean',
        'notification_sent_to_users' => 'boolean',
        'completion_notification_sent' => 'boolean',
        'display_banner' => 'boolean',
        'rollback_available' => 'boolean',
    ];

    // Relationships
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rollbackPerformer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class, 'related_maintenance_id');
    }

    // Scopes
    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('scheduled_start', '>=', now())
                     ->where('scheduled_start', '<=', now()->addDays($days))
                     ->whereIn('status', ['planned', 'notified']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress')
                     ->orWhere(function($q) {
                         $q->where('status', 'notified')
                           ->where('scheduled_start', '<=', now())
                           ->where('scheduled_end', '>=', now());
                     });
    }

    public function scopeRequiresApproval($query)
    {
        return $query->whereNull('approved_at')
                     ->where('maintenance_type', 'emergency_maintenance');
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'planned' => 'info',
            'notified' => 'primary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    public function getImpactBadgeColorAttribute(): string
    {
        return match($this->impact_level) {
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
            default => 'secondary'
        };
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->actual_start || !$this->actual_end) {
            return null;
        }
        
        return $this->actual_start->diffInMinutes($this->actual_end);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_start->isFuture() && 
               in_array($this->status, ['planned', 'notified']);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'in_progress' ||
               ($this->status === 'notified' && 
                $this->scheduled_start->isPast() && 
                $this->scheduled_end->isFuture());
    }

    public function getTimeUntilStartAttribute(): ?string
    {
        if (!$this->is_upcoming) {
            return null;
        }
        
        return $this->scheduled_start->diffForHumans();
    }

    // Business Logic Methods
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start' => now(),
        ]);

        // Send notification to users if banner display is enabled
        if ($this->display_banner && !$this->notification_sent_to_users) {
            $this->notifyUsers('maintenance_started');
        }
    }

    public function complete(string $notes = null, array $issues = []): void
    {
        $this->update([
            'status' => 'completed',
            'actual_end' => now(),
            'completion_notes' => $notes,
            'issues_encountered' => !empty($issues) ? json_encode($issues) : null,
            'actual_downtime_minutes' => $this->actual_start ? 
                $this->actual_start->diffInMinutes(now()) : null,
        ]);

        // Send completion notification
        if (!$this->completion_notification_sent) {
            $this->notifyUsers('maintenance_completed');
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'completion_notes' => $reason,
        ]);
    }

    public function markAsFailed(string $reason, array $issues = []): void
    {
        $this->update([
            'status' => 'failed',
            'actual_end' => now(),
            'issues_encountered' => json_encode($issues),
            'completion_notes' => $reason,
        ]);
    }

    public function rollback(int $userId, string $reason): void
    {
        $this->update([
            'rolled_back_at' => now(),
            'rolled_back_by' => $userId,
            'rollback_reason' => $reason,
            'status' => 'cancelled',
        ]);
    }

    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function notifyUsers(string $type): void
    {
        // This will be called by a service/job to create system notifications
        $notificationData = match($type) {
            'scheduled' => [
                'notification_sent_to_users' => true,
                'notification_sent_at' => now(),
            ],
            'maintenance_started' => [
                'notification_sent_to_users' => true,
                'notification_sent_at' => now(),
            ],
            'maintenance_completed' => [
                'completion_notification_sent' => true,
                'completion_notification_sent_at' => now(),
            ],
            default => []
        };

        if (!empty($notificationData)) {
            $this->update($notificationData);
        }
    }

    public function incrementNotifiedUsersCount(int $count): void
    {
        $this->increment('users_notified_count', $count);
    }
}

