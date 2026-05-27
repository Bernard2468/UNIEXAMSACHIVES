<?php

namespace App\Models;

use App\Forms\BaseFormDefinition;
use App\Forms\FormRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_slug',
        'form_code',
        'reference',
        'title',
        'status',
        'created_by',
        'current_stage',
        'current_assignee_id',
        'current_office_id',
        'section_data',
        'workflow_history',
        'requisition_amount',
        'priority',
        'referred_to_vc',
        'submitted_at',
        'completed_at',
        'rejected_at',
        'archived_at',
    ];

    protected $casts = [
        'section_data'       => 'array',
        'workflow_history'   => 'array',
        'requisition_amount' => 'decimal:2',
        'referred_to_vc'     => 'boolean',
        'submitted_at'       => 'datetime',
        'completed_at'       => 'datetime',
        'rejected_at'        => 'datetime',
        'archived_at'        => 'datetime',
    ];

    public const STATUS_DRAFT       = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_REJECTED    = 'rejected';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_ARCHIVED    = 'archived';

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentAssignee()
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    public function currentOffice()
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function signatures()
    {
        return $this->hasMany(FormSignature::class)->orderBy('signed_at');
    }

    public function attachments()
    {
        return $this->hasMany(FormAttachment::class)->latest();
    }

    public function comments()
    {
        return $this->hasMany(FormComment::class)->oldest();
    }

    /**
     * Resolve the FormDefinition class for this submission.
     */
    public function definition(): ?BaseFormDefinition
    {
        return app(FormRegistry::class)->find($this->form_slug);
    }

    /**
     * Section data for a single stage, or an empty array.
     */
    public function sectionData(string $stageSlug): array
    {
        $all = $this->section_data ?? [];

        return $all[$stageSlug] ?? [];
    }

    /**
     * Persist section data for a stage, leaving other stages untouched.
     */
    public function setSectionData(string $stageSlug, array $data): void
    {
        $all = $this->section_data ?? [];
        $all[$stageSlug] = $data;
        $this->section_data = $all;
    }

    /**
     * Append-only audit entry, mirroring the UIMMS workflow_history pattern.
     */
    public function appendHistory(string $action, ?int $userId, array $details = []): void
    {
        $history = $this->workflow_history ?? [];
        $history[] = [
            'action'    => $action,
            'user_id'   => $userId,
            'details'   => $details,
            'timestamp' => now()->toISOString(),
        ];
        $this->workflow_history = $history;
    }

    /**
     * Number of whole days since this submission last moved.
     * Only meaningful for in_progress forms; returns null otherwise.
     */
    public function getStaleDaysAttribute(): ?int
    {
        if ($this->status !== self::STATUS_IN_PROGRESS || !$this->updated_at) {
            return null;
        }
        return (int) $this->updated_at->diffInDays(now());
    }

    /**
     * Severity bucket for the stale-days pill: null (fresh / not in_progress),
     * 'warn' (2–6 days), or 'danger' (7+ days). Drives portal & show colors.
     */
    public function getStaleSeverityAttribute(): ?string
    {
        $days = $this->stale_days;
        if ($days === null) return null;
        if ($days >= 7) return 'danger';
        if ($days >= 2) return 'warn';
        return null;
    }

    public function scopeAwaitingUser(Builder $query, int $userId): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS)
            ->where('current_assignee_id', $userId);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }
}
