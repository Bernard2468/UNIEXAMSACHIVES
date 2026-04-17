<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemDocumentation extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the user who created this documentation
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is PDF
     */
    public function isPdf()
    {
        return strtolower($this->file_type) === 'pdf';
    }

    /**
     * Check if file is Word document
     */
    public function isWord()
    {
        return in_array(strtolower($this->file_type), ['doc', 'docx']);
    }

    /**
     * Check if file is ZIP archive
     */
    public function isZip()
    {
        return strtolower($this->file_type) === 'zip';
    }
}
