<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * All users assigned to this office.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'office_user')
            ->withPivot(['is_head', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Active members only — the candidates a form can be routed to.
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * The designated head of the office. Returned as a User or null.
     */
    public function head()
    {
        return $this->activeUsers()->wherePivot('is_head', true)->first();
    }

    /**
     * Forms currently assigned to (awaiting action by) this office.
     */
    public function pendingSubmissions()
    {
        return $this->hasMany(FormSubmission::class, 'current_office_id')
            ->where('status', 'in_progress');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
