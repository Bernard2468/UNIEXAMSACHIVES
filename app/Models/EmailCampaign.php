<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $table = 'comm_campaigns';

    protected $fillable = [
        'subject',
        'message',
        'attachments',
        'recipient_type',
        'selected_users',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'created_by',
        'reference',
        // UIMMS fields
        'memo_status',
        'current_assignee_id',
        'original_sender_id',
        'assigned_to_office',
        'priority',
        'due_date',
        'completed_at',
        'suspended_at',
        'archived_at',
        'workflow_history',
    ];

    protected $casts = [
        'attachments' => 'array',
        'selected_users' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        // UIMMS casts
        'workflow_history' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Single source of truth: memo_status is never null in the app.
     * DB column is nullable for legacy/backward compatibility; treat null as "pending".
     */
    public function getMemoStatusAttribute($value): string
    {
        return $value ?? 'pending';
    }

    /**
     * Whether this memo is in a state that allows urgency alerts and other "active" actions.
     */
    public function isPending(): bool
    {
        return $this->memo_status === 'pending';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // UIMMS Relationships
    public function currentAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    public function originalSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id');
    }

    public function sentRecipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id')->where('status', 'sent');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(MemoReply::class, 'campaign_id');
    }

    public function getRepliesCountAttribute()
    {
        return $this->replies()->count();
    }

    public function failedRecipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id')->where('status', 'failed');
    }

    public function pendingRecipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id')->where('status', 'pending');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }

    public function getSuccessRateAttribute(): float
    {
        $totalSent = $this->sent_count + $this->failed_count;
        if ($totalSent === 0) {
            return 0;
        }
        return round(($this->sent_count / $totalSent) * 100, 2);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeScheduledForSending($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }

    // UIMMS Methods
    public function activeParticipants(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id')
                    ->where('is_active_participant', true);
    }

    public function allParticipants(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'comm_campaign_id');
    }

    public function chatMessages()
    {
        return $this->replies()->with('user')->orderBy('created_at', 'asc');
    }

    public function getLastMessageAttribute()
    {
        return $this->replies()->latest()->first();
    }

    public function getActiveParticipantsCountAttribute()
    {
        return $this->activeParticipants()->count();
    }

    public function isActiveParticipant($userId)
    {
        return $this->activeParticipants()->where('user_id', $userId)->exists();
    }

    public function assignTo($userId, $assignedBy, $office = null)
    {
        // Deactivate current active participants EXCEPT the assigner
        $this->activeParticipants()->where('user_id', '!=', $assignedBy)->update(['is_active_participant' => false]);
        
        // Add new assignee as active participant
        $recipient = $this->recipients()->where('user_id', $userId)->first();
        if (!$recipient) {
            $recipient = $this->recipients()->create([
                'user_id' => $userId,
                'status' => 'sent',
                'is_active_participant' => true,
                'assigned_at' => now(),
                'last_activity_at' => now(),
            ]);
        } else {
            $recipient->update([
                'is_active_participant' => true,
                'assigned_at' => now(),
                'last_activity_at' => now(),
            ]);
        }

        // Ensure the assigner (person who made the assignment) remains an active participant
        $assignerRecipient = $this->recipients()->where('user_id', $assignedBy)->first();
        if (!$assignerRecipient) {
            $assignerRecipient = $this->recipients()->create([
                'user_id' => $assignedBy,
                'status' => 'sent',
                'is_active_participant' => true,
                'assigned_at' => now(),
                'last_activity_at' => now(),
            ]);
        } else {
            $assignerRecipient->update([
                'is_active_participant' => true,
                'last_activity_at' => now(),
            ]);
        }

        // Update memo assignment
        $this->update([
            'current_assignee_id' => $userId,
            'assigned_to_office' => $office,
            'memo_status' => 'pending',
        ]);

        // Add to workflow history
        $this->addToWorkflowHistory('assigned', $assignedBy, $userId, $office);

        return $recipient;
    }

    public function assignToMultiple($userIds, $assignedBy, $office = null)
    {
        // Ensure userIds is an array
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        // Deactivate current active participants EXCEPT the assigner
        $this->activeParticipants()->where('user_id', '!=', $assignedBy)->update(['is_active_participant' => false]);
        
        $assignedRecipients = [];
        
        // Add all new assignees as active participants
        foreach ($userIds as $userId) {
            $recipient = $this->recipients()->where('user_id', $userId)->first();
            if (!$recipient) {
                $recipient = $this->recipients()->create([
                    'user_id' => $userId,
                    'status' => 'sent',
                    'is_active_participant' => true,
                    'assigned_at' => now(),
                    'last_activity_at' => now(),
                ]);
            } else {
                $recipient->update([
                    'is_active_participant' => true,
                    'assigned_at' => now(),
                    'last_activity_at' => now(),
                ]);
            }
            $assignedRecipients[] = $recipient;
        }

        // Ensure the assigner (person who made the assignment) remains an active participant
        $assignerRecipient = $this->recipients()->where('user_id', $assignedBy)->first();
        if (!$assignerRecipient) {
            $assignerRecipient = $this->recipients()->create([
                'user_id' => $assignedBy,
                'status' => 'sent',
                'is_active_participant' => true,
                'assigned_at' => now(),
                'last_activity_at' => now(),
            ]);
        } else {
            $assignerRecipient->update([
                'is_active_participant' => true,
                'last_activity_at' => now(),
            ]);
        }

        // Update memo assignment - set first assignee as primary (for backward compatibility)
        $firstAssigneeId = !empty($userIds) ? $userIds[0] : null;
        $this->update([
            'current_assignee_id' => $firstAssigneeId,
            'assigned_to_office' => $office,
            'memo_status' => 'pending',
        ]);

        // Add to workflow history with all assignees
        $assigneesList = implode(', ', $userIds);
        $this->addToWorkflowHistory('assigned_multiple', $assignedBy, $assigneesList, $office);

        return $assignedRecipients;
    }

    public function addToWorkflowHistory($action, $userId, $targetUserId = null, $details = null)
    {
        $history = $this->workflow_history ?? [];
        $history[] = [
            'action' => $action,
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['workflow_history' => $history]);
    }

    public function markAsCompleted($userId)
    {
        $this->update([
            'memo_status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->addToWorkflowHistory('completed', $userId);
    }

    public function markAsSuspended($userId, $reason = null)
    {
        $this->update([
            'memo_status' => 'suspended',
            'suspended_at' => now(),
        ]);

        $this->addToWorkflowHistory('suspended', $userId, null, $reason);
    }

    public function markAsUnsuspended($userId)
    {
        $this->update([
            'memo_status' => 'pending',
            'suspended_at' => null,
        ]);

        $this->addToWorkflowHistory('unsuspended', $userId);
    }

    public function markAsArchived($userId)
    {
        $this->update([
            'memo_status' => 'archived',
            'archived_at' => now(),
        ]);

        $this->addToWorkflowHistory('archived', $userId);
    }

    // UIMMS Scopes
    public function scopeByMemoStatus($query, $status)
    {
        return $query->where('memo_status', $status);
    }

    public function scopePendingMemos($query)
    {
        return $query->where('memo_status', 'pending');
    }

    public function scopeSuspendedMemos($query)
    {
        return $query->where('memo_status', 'suspended');
    }

    public function scopeCompletedMemos($query)
    {
        return $query->where('memo_status', 'completed');
    }

    public function scopeArchivedMemos($query)
    {
        return $query->where('memo_status', 'archived');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('current_assignee_id', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get users who have bookmarked this memo
     */
    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memo_user_bookmarks', 'campaign_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Record that the given user has "read" this memo (opened the chat).
     * Used to determine Active Chat vs Read for pending memos.
     */
    public function recordLastReadBy(int $userId): void
    {
        \App\Models\MemoUserRead::updateOrCreate(
            [
                'user_id' => $userId,
                'campaign_id' => $this->id,
            ],
            [
                'last_read_at' => now(),
            ]
        );
    }

    /**
     * Get when the given user last read this memo, or null if never.
     */
    public function getLastReadAtForUser(int $userId): ?\Carbon\Carbon
    {
        $read = \App\Models\MemoUserRead::where('user_id', $userId)
            ->where('campaign_id', $this->id)
            ->first();

        return $read?->last_read_at;
    }

    /**
     * Check if a user has bookmarked this memo
     */
    public function isBookmarkedBy($userId): bool
    {
        return $this->bookmarkedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Get the user who suspended this memo
     */
    public function getSuspendedBy()
    {
        if ($this->memo_status !== 'suspended') {
            return null;
        }

        $workflowHistory = $this->workflow_history ?? [];
        
        // Find the most recent 'suspended' action
        foreach (array_reverse($workflowHistory) as $entry) {
            if ($entry['action'] === 'suspended') {
                return $entry['user_id'];
            }
        }

        return null;
    }

    /**
     * Check if the given user can unsuspend this memo
     */
    public function canUnsuspend($userId)
    {
        if ($this->memo_status !== 'suspended') {
            return false;
        }

        $suspendedBy = $this->getSuspendedBy();
        return $suspendedBy && $suspendedBy == $userId;
    }

    /**
     * Count pending memos that are "unread" for the user (same logic as portal Unread tab).
     * Includes memos where user is creator and there is activity (so sender stays updated).
     * Activity = has replies OR updated_at > created_at (covers assign, status change, etc.).
     * Used for the sidebar Memos Portal counter.
     */
    public static function countUnreadPendingForUser(int $userId): int
    {
        $memos = static::with(['replies' => fn ($q) => $q->select('campaign_id', 'created_at')])
            ->where(function ($query) use ($userId) {
                $query->whereHas('activeParticipants', fn ($q) => $q->where('user_id', $userId))
                    ->orWhereHas('recipients', fn ($q) => $q->where('user_id', $userId))
                    ->orWhereHas('replies', fn ($q) => $q->where('reply_mode', 'specific')->whereJsonContains('specific_recipients', (string) $userId))
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('created_by', $userId)
                            ->where(function ($q2) {
                                $q2->whereHas('replies')->orWhereRaw('updated_at > created_at');
                            });
                    });
            })
            ->where(function ($query) {
                $query->where('memo_status', 'pending')->orWhereNull('memo_status');
            })
            ->whereDoesntHave('bookmarkedBy', fn ($q) => $q->where('user_id', $userId))
            ->get(['id', 'updated_at', 'created_at', 'created_by']);

        if ($memos->isEmpty()) {
            return 0;
        }

        $campaignIds = $memos->pluck('id')->toArray();
        $lastReads = \App\Models\MemoUserRead::where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->get()
            ->keyBy('campaign_id');

        $count = 0;
        foreach ($memos as $memo) {
            // Creator-only memos: count only if there is activity (replies or update)
            if ($memo->created_by == $userId) {
                $hasActivity = ! $memo->replies->isEmpty() || \Carbon\Carbon::parse($memo->updated_at ?? $memo->created_at)->greaterThan(\Carbon\Carbon::parse($memo->created_at));
                if (! $hasActivity) {
                    continue;
                }
            }
            $latestReplyAt = $memo->replies->isEmpty() ? null : $memo->replies->max('created_at');
            $memoUpdated = $memo->updated_at ?? $memo->created_at;
            $latestActivityAt = $latestReplyAt
                ? (\Carbon\Carbon::parse($memoUpdated)->greaterThan(\Carbon\Carbon::parse($latestReplyAt)) ? \Carbon\Carbon::parse($memoUpdated) : \Carbon\Carbon::parse($latestReplyAt))
                : \Carbon\Carbon::parse($memoUpdated);
            $lastReadAt = $lastReads->get($memo->id)?->last_read_at;
            if ($lastReadAt === null || $latestActivityAt->greaterThan($lastReadAt)) {
                $count++;
            }
        }

        return $count;
    }
}