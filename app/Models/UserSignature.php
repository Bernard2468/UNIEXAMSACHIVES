<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'signature_image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->signature_image_path) {
            return asset('storage/' . ltrim($this->signature_image_path, '/'));
        }

        return null;
    }
}
