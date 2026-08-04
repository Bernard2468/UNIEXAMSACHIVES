<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BotApiKey;
use App\Models\BotConversation;
use App\Models\BotFeedback;
use App\Models\BotKnowledgeEntry;
use App\Models\BotUsageDaily;
use App\Models\SystemSetting;
use App\Services\Bot\GeminiClient;
use App\Services\Bot\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The Super Admin "Bot" control center: master switch, encrypted key vault,
 * usage caps, privacy-safe analytics and the editable knowledge base.
 */
class BotController extends Controller
{
    /** Nominal Gemini-flash cost per answered call (USD) for the cost estimate. */
    private const COST_PER_CALL = 0.0004;

    /** Bot config settings self-provisioned on first visit (SystemSetting::set only updates existing rows). */
    private function ensureSettings(): void
    {
        $defaults = [
            ['key' => 'bot_enabled',           'value' => '1',   'data_type' => 'boolean', 'label' => 'Bot Enabled (master switch)'],
            ['key' => 'bot_daily_user_cap',    'value' => '40',  'data_type' => 'integer', 'label' => 'Daily Gemini messages per user (0 = unlimited)'],
            ['key' => 'bot_store_transcripts', 'value' => '0',   'data_type' => 'boolean', 'label' => 'Store full conversation transcripts'],
            ['key' => 'bot_model_cascade',     'value' => json_encode(GeminiClient::DEFAULT_MODELS), 'data_type' => 'json', 'label' => 'Gemini model cascade'],
            ['key' => 'bot_greeting',          'value' => "Hi there! I'm your UDTS Assistant. I know every corner of this platform — ask me about forms, memos, the archive, or how to get anything done. I can also take you straight to where you need to go.", 'data_type' => 'string', 'label' => 'Welcome greeting'],
            ['key' => 'bot_temperature',       'value' => '0.6', 'data_type' => 'string',  'label' => 'Answer creativity (temperature)'],
        ];

        foreach ($defaults as $d) {
            SystemSetting::firstOrCreate(
                ['key' => $d['key']],
                [
                    'value'       => $d['value'],
                    'category'    => 'ai_bot',
                    'label'       => $d['label'],
                    'description' => '',
                    'data_type'   => $d['data_type'],
                    'is_public'   => false,
                    'is_editable' => true,
                ],
            );
        }
    }

    public function index()
    {
        $this->ensureSettings();

        $settings = [
            'bot_enabled'           => (bool) SystemSetting::get('bot_enabled', true),
            'bot_daily_user_cap'    => (int) SystemSetting::get('bot_daily_user_cap', 40),
            'bot_store_transcripts' => (bool) SystemSetting::get('bot_store_transcripts', false),
            'bot_model_cascade'     => $this->cascadeString(),
            'bot_greeting'          => (string) SystemSetting::get('bot_greeting', ''),
            'bot_temperature'       => (string) SystemSetting::get('bot_temperature', '0.6'),
        ];

        $keys = BotApiKey::orderBy('provider')->orderByDesc('is_active')->get();

        $analytics = [
            'today' => $this->aggregate(now()->toDateString(), now()->toDateString()),
            'week'  => $this->aggregate(now()->subDays(6)->toDateString(), now()->toDateString()),
            'month' => $this->aggregate(now()->subDays(29)->toDateString(), now()->toDateString()),
        ];

        $series      = $this->dailySeries(14);
        $feedback    = $this->feedbackSummary();
        $flagged     = $this->flaggedTopics();
        $kbEntries   = BotKnowledgeEntry::orderByDesc('priority')->orderBy('title')->get();
        $transcripts = $settings['bot_store_transcripts']
            ? BotConversation::with('user')->latest()->limit(25)->get()
            : collect();

        return view('super-admin.bot.index', compact(
            'settings', 'keys', 'analytics', 'series', 'feedback', 'flagged', 'kbEntries', 'transcripts'
        ));
    }

    private function cascadeString(): string
    {
        $val = SystemSetting::get('bot_model_cascade');
        if (is_array($val)) {
            return implode("\n", $val);
        }
        $decoded = json_decode((string) $val, true);
        return is_array($decoded) ? implode("\n", $decoded) : (string) $val;
    }

    // ── Settings ─────────────────────────────────────────────────────────────

    public function updateSettings(Request $request)
    {
        $this->ensureSettings();

        $data = $request->validate([
            'bot_daily_user_cap'    => 'required|integer|min:0|max:100000',
            'bot_temperature'       => 'required|numeric|min:0|max:1',
            'bot_store_transcripts' => 'nullable|boolean',
            'bot_greeting'          => 'nullable|string|max:1000',
            'bot_model_cascade'     => 'nullable|string|max:2000',
        ]);

        SystemSetting::set('bot_daily_user_cap', (int) $data['bot_daily_user_cap'], auth()->id());
        SystemSetting::set('bot_temperature', (string) $data['bot_temperature'], auth()->id());
        SystemSetting::set('bot_store_transcripts', $request->boolean('bot_store_transcripts'), auth()->id());
        SystemSetting::set('bot_greeting', (string) ($data['bot_greeting'] ?? ''), auth()->id());

        $models = collect(preg_split('/[\r\n,]+/', (string) ($data['bot_model_cascade'] ?? '')))
            ->map(fn ($m) => trim($m))
            ->filter()
            ->values()
            ->all();
        if (!empty($models)) {
            // Pass the array directly — setTypedValue() json-encodes json-typed values,
            // so pre-encoding here would double-encode it.
            SystemSetting::set('bot_model_cascade', $models, auth()->id());
        }

        return back()->with('success', 'Bot settings updated.');
    }

    /** Master switch — turns the bot on/off for the entire system. */
    public function toggle(Request $request)
    {
        $this->ensureSettings();
        $enabled = $request->boolean('enabled');
        SystemSetting::set('bot_enabled', $enabled, auth()->id());
        Cache::forget('system_setting_bot_enabled');

        return back()->with('success', $enabled
            ? 'Bot is now LIVE on every user page.'
            : 'Bot is now switched OFF everywhere.');
    }

    // ── API key vault ────────────────────────────────────────────────────────

    public function storeKey(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|in:gemini,deepseek',
            'label'    => 'nullable|string|max:60',
            'api_key'  => 'required|string|max:400',
        ]);

        BotApiKey::storeKey($data['api_key'], $data['provider'], $data['label'] ?? null);

        return back()->with('success', 'API key added securely (encrypted at rest).');
    }

    public function toggleKey(BotApiKey $key)
    {
        $key->is_active = !$key->is_active;
        $key->save();

        return back()->with('success', 'API key ' . ($key->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroyKey(BotApiKey $key)
    {
        $key->delete();
        return back()->with('success', 'API key removed.');
    }

    public function testKey(BotApiKey $key, GeminiClient $gemini)
    {
        $plain = $key->plainKey();
        if (!$plain) {
            return back()->with('error', 'Could not decrypt that key.');
        }

        if ($key->provider !== 'gemini') {
            return back()->with('error', 'Test is currently only supported for Gemini keys.');
        }

        $result = $gemini->testRawKey($plain);
        $key->markUsed(!$result['ok']);

        return $result['ok']
            ? back()->with('success', "✅ {$result['message']} (model: {$result['model']})")
            : back()->with('error', "❌ Test failed: {$result['message']}");
    }

    // ── Knowledge base CRUD ──────────────────────────────────────────────────

    public function storeEntry(Request $request)
    {
        $data = $this->validateEntry($request);
        BotKnowledgeEntry::create($this->entryPayload($data));
        Cache::forget('bot_kb_db_entries');

        return back()->with('success', 'Knowledge entry added.');
    }

    public function updateEntry(Request $request, BotKnowledgeEntry $entry)
    {
        $data = $this->validateEntry($request);
        $entry->update($this->entryPayload($data));
        Cache::forget('bot_kb_db_entries');

        return back()->with('success', 'Knowledge entry updated.');
    }

    public function destroyEntry(BotKnowledgeEntry $entry)
    {
        $entry->delete();
        Cache::forget('bot_kb_db_entries');

        return back()->with('success', 'Knowledge entry deleted.');
    }

    private function validateEntry(Request $request): array
    {
        return $request->validate([
            'category'    => 'nullable|string|max:60',
            'title'       => 'required|string|max:160',
            'keywords'    => 'required|string|max:1000',
            'answer'      => 'required|string|max:8000',
            'priority'    => 'nullable|integer|min:0|max:1000',
            'is_active'   => 'nullable|boolean',
            'link_labels' => 'nullable|array',
            'link_urls'   => 'nullable|array',
        ]);
    }

    private function entryPayload(array $data): array
    {
        $links = [];
        $labels = $data['link_labels'] ?? [];
        $urls   = $data['link_urls'] ?? [];
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            $url   = trim((string) ($urls[$i] ?? ''));
            if ($label !== '' && $url !== '') {
                $links[] = ['label' => $label, 'url' => $url];
            }
        }

        return [
            'category'  => $data['category'] ?? 'general',
            'title'     => $data['title'],
            'keywords'  => $data['keywords'],
            'answer'    => $data['answer'],
            'links'     => $links,
            'priority'  => (int) ($data['priority'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    // ── Analytics (privacy-safe: counts only) ────────────────────────────────

    private function aggregate(string $from, string $to): array
    {
        $row = BotUsageDaily::whereBetween('date', [$from, $to])
            ->selectRaw('COALESCE(SUM(messages),0) as messages')
            ->selectRaw('COALESCE(SUM(kb_answers),0) as kb_answers')
            ->selectRaw('COALESCE(SUM(live_answers),0) as live_answers')
            ->selectRaw('COALESCE(SUM(cache_hits),0) as cache_hits')
            ->selectRaw('COALESCE(SUM(api_calls),0) as api_calls')
            ->selectRaw('COALESCE(SUM(latency_ms_sum),0) as latency_sum')
            ->selectRaw('COUNT(DISTINCT user_id) as active_users')
            ->first();

        $messages = (int) ($row->messages ?? 0);
        $api      = (int) ($row->api_calls ?? 0);
        $local    = max(0, $messages - $api);

        return [
            'messages'      => $messages,
            'kb_answers'    => (int) ($row->kb_answers ?? 0),
            'live_answers'  => (int) ($row->live_answers ?? 0),
            'cache_hits'    => (int) ($row->cache_hits ?? 0),
            'api_calls'     => $api,
            'local_answers' => $local,
            'active_users'  => (int) ($row->active_users ?? 0),
            'avg_latency'   => $messages > 0 ? (int) round(((int) $row->latency_sum) / $messages) : 0,
            'pct_local'     => $messages > 0 ? (int) round($local / $messages * 100) : 0,
            'est_cost'      => round($api * self::COST_PER_CALL, 2),
        ];
    }

    /** Per-day totals for the last N days (for the chart). */
    private function dailySeries(int $days): array
    {
        $rows = BotUsageDaily::where('date', '>=', now()->subDays($days - 1)->toDateString())
            ->select('date')
            ->selectRaw('SUM(messages) as messages')
            ->selectRaw('SUM(api_calls) as api_calls')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => \Illuminate\Support\Carbon::parse($r->date)->toDateString());

        $labels = $messages = $api = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->toDateString();
            $labels[]   = $d->format('M j');
            $messages[] = (int) optional($rows->get($key))->messages;
            $api[]      = (int) optional($rows->get($key))->api_calls;
        }

        return ['labels' => $labels, 'messages' => $messages, 'api' => $api];
    }

    private function feedbackSummary(): array
    {
        $up   = BotFeedback::where('rating', 'up')->count();
        $down = BotFeedback::where('rating', 'down')->count();
        return ['up' => $up, 'down' => $down, 'total' => $up + $down];
    }

    /** Topics that drew 👎 — a safe signal of what to improve (no user content). */
    private function flaggedTopics()
    {
        return BotFeedback::where('rating', 'down')
            ->whereNotNull('matched_key')
            ->select('matched_key', DB::raw('COUNT(*) as c'))
            ->groupBy('matched_key')
            ->orderByDesc('c')
            ->limit(10)
            ->get();
    }
}
