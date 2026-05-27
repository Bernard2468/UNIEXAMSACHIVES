<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Leadership categories used by the forms workflow to route to the
     * right "pool" of people (every department has its own HOD, every
     * faculty its own Dean, and Directors are distinct roles altogether).
     */
    public const CATEGORY_HOD      = 'hod';
    public const CATEGORY_DEAN     = 'dean';
    public const CATEGORY_DIRECTOR = 'director';

    public const CATEGORIES = [
        self::CATEGORY_HOD      => 'Head of Department',
        self::CATEGORY_DEAN     => 'Dean',
        self::CATEGORY_DIRECTOR => 'Director',
    ];

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function categoryLabel(): ?string
    {
        return self::CATEGORIES[$this->category] ?? null;
    }
}
