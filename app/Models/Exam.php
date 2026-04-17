<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'exam_date' => 'datetime',
    ];

    public function file()
    {
        return $this->hasMany(File::class);
    }
}
