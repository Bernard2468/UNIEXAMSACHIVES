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
