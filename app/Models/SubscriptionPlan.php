<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features',
        'is_active',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'features'    => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'price'       => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SubscriptionPlan $plan) {
            $plan->slug = static::generateUniqueSlug($plan->name);
        });

        static::updating(function (SubscriptionPlan $plan) {
            if ($plan->isDirty('name')) {
                $plan->slug = static::generateUniqueSlug($plan->name, $plan->id);
            }
        });
    }

    private static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'monthly'     => 'per month',
            'quarterly'   => 'per quarter',
            'semi_annual' => 'every 6 months',
            'annual'      => 'per year',
            'one_time'    => 'one-time payment',
            default       => ucfirst(str_replace('_', ' ', $this->billing_cycle)),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('price');
    }
}
