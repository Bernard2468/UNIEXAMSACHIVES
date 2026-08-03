<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * Save (or replace) a user's reusable signature from a base64 PNG data
     * URI. Shared by the settings page, form signing, and memo minuting so
     * they all write the saved signature the same way. Returns null when the
     * payload is not valid base64.
     */
    public static function saveFromBase64(int $userId, string $data): ?self
    {
        if (str_starts_with($data, 'data:')) {
            $commaPos = strpos($data, ',');
            $data = $commaPos !== false ? substr($data, $commaPos + 1) : '';
        }

        $binary = base64_decode($data, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        $disk = Storage::disk('public');

        // Replace any existing saved-signature file so user-signatures/ never
        // accumulates orphan PNGs on re-save.
        $existing = static::where('user_id', $userId)->first();
        if ($existing && $existing->signature_image_path && $disk->exists($existing->signature_image_path)) {
            $disk->delete($existing->signature_image_path);
        }

        $path = 'user-signatures/' . $userId . '-' . Str::random(8) . '.png';
        $disk->put($path, $binary);

        return static::updateOrCreate(
            ['user_id' => $userId],
            ['signature_image_path' => $path],
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->signature_image_path) {
            return asset('storage/' . ltrim($this->signature_image_path, '/'));
        }

        return null;
    }
}
