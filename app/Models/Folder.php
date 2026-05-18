<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Folder extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'file_folder');
    }

    public function approvedFiles()
    {
        return $this->belongsToMany(File::class, 'file_folder')->where('is_approve', 1);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_folder');
    }

    /**
     * Users this folder has been shared with.
     * Pivot carries: permission ('viewer'|'editor'), shared_by, timestamps.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'folder_shares')
            ->withPivot('permission', 'shared_by')
            ->withTimestamps();
    }

    public function viewers()
    {
        return $this->members()->wherePivot('permission', 'viewer');
    }

    public function editors()
    {
        return $this->members()->wherePivot('permission', 'editor');
    }

    /**
     * Owner short-circuits all access checks. Used everywhere.
     */
    public function isOwnedBy(?User $user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    /**
     * Anyone with read access: owner or any member (viewer/editor).
     * Sharing intentionally bypasses the folder password — once an owner
     * grants access, the share IS the access. The password is for
     * un-trusted visitors only.
     */
    public function isAccessibleBy(?User $user): bool
    {
        if (!$user) return false;
        if ($this->isOwnedBy($user)) return true;
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Write access: owner, or an explicit editor.
     * Editors can add their OWN files/exams to the folder, but only the
     * owner can remove items or manage members.
     */
    public function canEditContents(?User $user): bool
    {
        if (!$user) return false;
        if ($this->isOwnedBy($user)) return true;
        return $this->editors()->where('users.id', $user->id)->exists();
    }
}