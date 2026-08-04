<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * An AI provider API key in the bot's safe vault.
 *
 * The raw key is only ever accepted through {@see self::storeKey()} which encrypts
 * it at rest. The plaintext is recovered on demand via {@see self::plainKey()} and
 * is never mass-assignable or serialised.
 */
class BotApiKey extends Model
{
    protected $table = 'bot_api_keys';

    protected $fillable = [
        'label', 'provider', 'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // key_encrypted / last4 are set explicitly, never mass-assigned.
    protected $hidden = ['key_encrypted'];

    /**
     * Create + persist a key from its plaintext value (encrypting at rest).
     */
    public static function storeKey(string $plain, string $provider = 'gemini', ?string $label = null): self
    {
        $plain = trim($plain);

        $key = new self();
        $key->label         = $label ?: ucfirst($provider) . ' key';
        $key->provider      = $provider;
        $key->key_encrypted = Crypt::encryptString($plain);
        $key->last4         = substr($plain, -4);
        $key->is_active     = true;
        $key->save();

        return $key;
    }

    /**
     * Decrypt and return the raw key. Returns null if the ciphertext is corrupt.
     */
    public function plainKey(): ?string
    {
        try {
            return Crypt::decryptString($this->key_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function maskedKey(): string
    {
        return '••••••••' . ($this->last4 ?: '');
    }

    /** Active keys for a provider, oldest-used first so the pool rotates fairly. */
    public static function pool(string $provider = 'gemini')
    {
        return static::where('provider', $provider)
            ->where('is_active', true)
            ->orderByRaw('last_used_at IS NULL DESC')
            ->orderBy('last_used_at', 'asc')
            ->get();
    }

    public function markUsed(bool $failed = false): void
    {
        $this->last_used_at   = now();
        $this->request_count  = $this->request_count + 1;
        if ($failed) {
            $this->failure_count = $this->failure_count + 1;
        }
        $this->save();
    }
}
