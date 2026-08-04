<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-user, per-day usage counters (counts only, no content). Drives the daily
 * Gemini cap and the aggregate analytics on the Super Admin Bot page.
 */
class BotUsageDaily extends Model
{
    protected $table = 'bot_usage_daily';

    protected $fillable = [
        'user_id', 'date', 'messages', 'kb_answers',
        'live_answers', 'cache_hits', 'api_calls', 'latency_ms_sum',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** Today's row for a user, created on first use. */
    public static function todayFor(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'date' => now()->toDateString()],
        );
    }

    /** How many Gemini calls this user has spent today. */
    public static function apiCallsToday(int $userId): int
    {
        return (int) static::where('user_id', $userId)
            ->where('date', now()->toDateString())
            ->value('api_calls');
    }

    /**
     * Record one answered message. `$source` is one of live|kb|cache|api and
     * increments the matching counter; only `api` counts against the daily cap.
     */
    public function record(string $source, int $latencyMs = 0): void
    {
        $this->messages      += 1;
        $this->latency_ms_sum += max(0, $latencyMs);

        match ($source) {
            'live'  => $this->live_answers += 1,
            'kb'    => $this->kb_answers   += 1,
            'cache' => $this->cache_hits   += 1,
            'api'   => $this->api_calls    += 1,
            default => null,
        };

        $this->save();
    }
}
