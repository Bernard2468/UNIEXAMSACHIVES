<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'category',
        'label',
        'description',
        'data_type',
        'is_public',
        'is_editable',
        'requires_restart',
        'validation_rules',
        'default_value',
        'updated_by'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
        'requires_restart' => 'boolean',
    ];

    // Relationships
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopePaymentGateway($query)
    {
        return $query->where('category', 'payment_gateway');
    }

    // Accessors & Mutators
    public function getTypedValueAttribute()
    {
        return match($this->data_type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value
        };
    }

    public function setTypedValue($value): void
    {
        $this->value = match($this->data_type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value
        };
    }

    // Static Helper Methods
    public static function get(string $key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function() use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->typed_value;
        });
    }

    public static function set(string $key, $value, int $updatedBy = null): bool
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $setting->setTypedValue($value);
        $setting->updated_by = $updatedBy;
        $result = $setting->save();
        
        // Clear cache
        Cache::forget("system_setting_{$key}");
        
        return $result;
    }

    public static function getPaystackPublicKey(): ?string
    {
        return static::get('paystack_public_key');
    }

    public static function getPaystackSecretKey(): ?string
    {
        return static::get('paystack_secret_key');
    }

    public static function getPaystackWebhookSecret(): ?string
    {
        return static::get('paystack_webhook_secret');
    }

    public static function isPaystackConfigured(): bool
    {
        return !empty(static::getPaystackPublicKey()) && 
               !empty(static::getPaystackSecretKey());
    }

    public static function getMaintenanceMode(): bool
    {
        return (bool) static::get('maintenance_mode', false);
    }

    public static function setMaintenanceMode(bool $enabled, int $updatedBy = null): bool
    {
        return static::set('maintenance_mode', $enabled, $updatedBy);
    }

    public static function getGracePeriodDays(): int
    {
        return (int) static::get('subscription_grace_period_days', 7);
    }

    public static function getAutoRenewalEnabled(): bool
    {
        return (bool) static::get('auto_renewal_enabled', true);
    }

    public static function getRenewalReminderDays(): array
    {
        $days = static::get('renewal_reminder_days', [30, 14, 7, 1]);
        return is_array($days) ? $days : json_decode($days, true) ?? [30, 14, 7, 1];
    }

    public static function getSubscriptionBasePrice(): float
    {
        return (float) static::get('subscription_base_price', 5000.00);
    }

    public static function getSubscriptionPriceForYears(int $years): float
    {
        $basePrice = static::getSubscriptionBasePrice();
        return $basePrice * $years;
    }

    // Business Logic
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
        });
    }
}

