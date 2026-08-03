<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $guarded = [];

    /** A Faculty OR School — top-level container that may hold departments. */
    public const TYPE_FACULTY = 'faculty';
    /** A department that lives under a faculty/school (via parent_id). */
    public const TYPE_DEPARTMENT = 'department';
    /** A standalone unit (Directorate, Office, …) — never nested, never a parent. */
    public const TYPE_UNIT = 'unit';

    public const TYPES = [
        self::TYPE_FACULTY,
        self::TYPE_DEPARTMENT,
        self::TYPE_UNIT,
    ];

    /** The faculty/school this department belongs to (null for faculties + units). */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /** The departments nested under this faculty/school. */
    public function departments()
    {
        return $this->hasMany(Department::class, 'parent_id')->orderBy('name');
    }

    public function isFaculty(): bool
    {
        return $this->type === self::TYPE_FACULTY;
    }

    public function isDepartment(): bool
    {
        return $this->type === self::TYPE_DEPARTMENT;
    }

    public function isUnit(): bool
    {
        return $this->type === self::TYPE_UNIT;
    }

    public function scopeFaculties($query)
    {
        return $query->where('type', self::TYPE_FACULTY);
    }

    public function scopeUnits($query)
    {
        return $query->where('type', self::TYPE_UNIT);
    }
}
