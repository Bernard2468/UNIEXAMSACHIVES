<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'stage_slug',
        'name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->path, '/'));
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
