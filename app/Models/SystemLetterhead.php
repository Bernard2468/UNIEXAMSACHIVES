<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLetterhead extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    /**
     * Restrict the query to the letterheads a given user is allowed to pick in
     * the memo composer. A letterhead is visible when it is global (no scope)
     * OR its scope matches one of the user's affiliations:
     *   - a Department in the user's set (primary + secondaries + their parent
     *     faculties — see User::letterheadDepartmentIds()), or
     *   - an Office the user is an active member of.
     */
    public function scopeVisibleTo($query, User $user)
    {
        $deptIds   = $user->letterheadDepartmentIds();
        $officeIds = $user->activeOffices()->pluck('offices.id');

        return $query->where(function ($q) use ($deptIds, $officeIds) {
            $q->whereNull('scope_type'); // global — everyone sees it

            if ($deptIds->isNotEmpty()) {
                $q->orWhere(fn ($s) => $s->where('scope_type', 'department')
                                         ->whereIn('scope_id', $deptIds));
            }

            if ($officeIds->isNotEmpty()) {
                $q->orWhere(fn ($s) => $s->where('scope_type', 'office')
                                         ->whereIn('scope_id', $officeIds));
            }
        });
    }

    /**
     * Resolve the org entity this letterhead is scoped to (or null for global).
     */
    public function getScopeEntityAttribute()
    {
        return match ($this->scope_type) {
            'department' => Department::find($this->scope_id),
            'office'     => Office::find($this->scope_id),
            default      => null,
        };
    }

    /**
     * Human-readable scope label for the admin management screen.
     */
    public function getScopeLabelAttribute(): string
    {
        if (!$this->scope_type) {
            return 'Everyone';
        }
        $entity = $this->scope_entity;
        if ($entity) {
            return $entity->name;
        }
        return $this->scope_type === 'office' ? 'Deleted office' : 'Deleted department';
    }

    /**
     * Resolve the public-facing URL for this letterhead's image. Stored values
     * are either remote URLs (legacy Cloudinary seeds) or paths relative to
     * the `public/` directory (admin-uploaded files — chosen over the public
     * disk because shared hosting disables symlink()).
     */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path;
        if (!$path) {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return asset($path);
    }

    /**
     * Look up a letterhead by the slug stored on a memo's `letterhead` column.
     */
    public static function findBySlug(?string $slug): ?self
    {
        if (!$slug) {
            return null;
        }
        return static::where('slug', $slug)->first();
    }
}
