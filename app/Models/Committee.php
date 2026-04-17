<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Get the users that belong to this committee
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'committee_user')
                    ->withTimestamps();
    }

    /**
     * Get active committees
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get inactive committees
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if committee is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
}

