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
            // NB: the opening greeting is generated dynamically per user (see the
            // bot-assistant widget) — there is intentionally no stored greeting.
            ['key' => 'bot_temperature',       'value' => '0.6', 'data_type' => 'string',  'label' => 'Answer creativity (temperature)'],
        ];

        foreach ($defaults as $d) {
            SystemSetting::firstOrCreate(
                ['key' => $d['key']],
                [
                    'value'       => $d['value'],
                    // 'category' is an ENUM on system_settings; 'ai_bot' is not a member.
                    // Bot settings are looked up by key (never grouped by category), so we
                    // file them under the existing 'api' category to satisfy the schema.
                    'category'    => 'api',
                    'label'       => $d['label'],
                    'description' => '',
                    'data_type'   => $d['data_type'],
                    'is_public'   => false,
                    'is_editable' => true,
                ],
            );
        }

        // Self-heal a cascade saved with now-retired model IDs (e.g. gemini-1.5-*),
        // which would otherwise 404 on every call.
        $cascade = SystemSetting::get('bot_model_cascade');
        $cascade = is_array($cascade) ? $cascade : (json_decode((string) $cascade, true) ?: []);
        $cleaned = array_values(array_filter($cascade, fn ($m) => !in_array(trim((string) $m), GeminiClient::RETIRED_MODELS, true)));
        if (empty($cleaned)) {
            $cleaned = GeminiClient::DEFAULT_MODELS;
        }
        if ($cleaned !== $cascade) {
            SystemSetting::set('bot_model_cascade', $cleaned, auth()->id());
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
            'bot_model_cascade'     => 'nullable|string|max:2000',
        ]);

        SystemSetting::set('bot_daily_user_cap', (int) $data['bot_daily_user_cap'], auth()->id());
        SystemSetting::set('bot_temperature', (string) $data['bot_temperature'], auth()->id());
        SystemSetting::set('bot_store_transcripts', $request->boolean('bot_store_transcripts'), auth()->id());
        // The opening greeting is generated dynamically per user — no stored value.

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

        if (!$result['ok']) {
            return back()->with('error', "❌ Test failed: {$result['message']}");
        }

        $models = $result['models'] ?? [];
        $preview = collect($models)->take(12)->implode(', ');
        $hint = $preview ? " Available models: {$preview}." : '';

        return back()->with('success', "✅ {$result['message']}{$hint} You can put any of these in the model cascade below.");
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

    /**
     * One-click loader for a comprehensive, security-cautious starter knowledge
     * base. Idempotent (firstOrCreate by title) so re-running never overwrites
     * entries you've edited — it only fills in ones that are missing.
     */
    public function seedKnowledge()
    {
        $created = 0;
        foreach ($this->starterKnowledge() as $e) {
            $entry = BotKnowledgeEntry::firstOrCreate(
                ['title' => $e['title']],
                [
                    'category'  => $e['category'] ?? 'general',
                    'keywords'  => $e['keywords'],
                    'answer'    => $e['answer'],
                    'links'     => $e['links'] ?? [],
                    'priority'  => $e['priority'] ?? 5,
                    'is_active' => true,
                ],
            );
            if ($entry->wasRecentlyCreated) {
                $created++;
            }
        }
        Cache::forget('bot_kb_db_entries');

        return back()->with('success', $created > 0
            ? "Loaded {$created} new knowledge entries. Edit or delete any of them below."
            : 'Starter knowledge is already loaded — nothing to add. Your existing entries were left untouched.');
    }

    private function u(string $route, array $params = []): string
    {
        try { return route($route, $params, false); } catch (\Throwable $e) { return '/'; }
    }

    /**
     * Comprehensive starter pack. Guidance only — no sensitive data, credentials
     * or internal secrets — so it is safe to expose to every signed-in user.
     * These extend the bot's built-in knowledge with deeper, form-by-form detail.
     *
     * @return array<int,array{category:string,title:string,keywords:string,answer:string,links:array,priority?:int}>
     */
    private function starterKnowledge(): array
    {
        $forms = [['label' => 'All Forms', 'url' => $this->u('admin.forms.gallery')], ['label' => 'Forms Portal', 'url' => $this->u('admin.forms.portal')]];
        $portal = [['label' => 'Forms Portal', 'url' => $this->u('admin.forms.portal')]];
        $memoPortal = [['label' => 'Memos Portal', 'url' => $this->u('dashboard.uimms.portal')]];
        $myFolders = [['label' => 'My Folders', 'url' => $this->u('dashboard.folders.index')]];
        $settings = [['label' => 'Settings', 'url' => $this->u('dashboard.settings')]];

        return [
            [
                'category' => 'forms', 'title' => 'Payment Requisition (PR) form',
                'keywords' => 'payment requisition pr request payment money reimbursement claim funds pay vendor',
                'answer' => "A **Payment Requisition (PR)** is how you request a payment. Raise it from **All Forms**, enter the amount and details, and submit — it then routes through the relevant offices (e.g. Finance) for signing. Track it in the **Forms Portal**.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => 'Purchase / Works Authorization (PWA) form',
                'keywords' => 'purchase works authorization pwa buy procurement order works authorise purchase request',
                'answer' => "A **Purchase/Works Authorization (PWA)** authorises a purchase or works. Raise it from **All Forms**, fill in the items/works, and submit for approval through the required offices. Follow its progress in the **Forms Portal**.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => 'Leave application (annual, casual, resumption)',
                'keywords' => 'leave annual leave casual leave apply for leave time off vacation resumption resume from leave leave form',
                'answer' => "Apply for leave from **All Forms** — choose **Annual Leave**, **Casual Leave**, or **Leave Resumption** (to report back). Submit it and it routes to your approver; watch it in the **Forms Portal**.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => 'Vehicle Maintenance Allowance form',
                'keywords' => 'vehicle maintenance allowance car allowance transport maintenance claim',
                'answer' => "The **Vehicle Maintenance Allowance** form is raised from **All Forms**. Complete the details and submit for the usual approval route.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => 'Renewal of Appointment & Promotion forms',
                'keywords' => 'renewal of appointment promotion appointment renew contract promote staff academic non-academic employee personal records',
                'answer' => "**Renewal of Appointment**, **Promotion** and **Employee Personal Records** forms are all raised from **All Forms** (academic and non-academic variants where applicable). Complete and submit; they route to the appropriate approvers.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => 'Reassign a form to someone else',
                'keywords' => 'reassign form forward form pass form wrong person delegate form send to another handoff',
                'answer' => "If a form reached you but should go to a colleague, open it in the **Forms Portal** and use **Reassign** (where your role permits). The form then moves to that person with the trail intact.",
                'links' => $portal,
            ],
            [
                'category' => 'forms', 'title' => 'Cancel or withdraw a form',
                'keywords' => 'cancel form withdraw form delete form stop form mistake recall form',
                'answer' => "The person who raised a form can **cancel** it from the **Forms Portal** while it's still in progress. Once cancelled it stops moving through the workflow.",
                'links' => $portal,
            ],
            [
                'category' => 'forms', 'title' => 'Save a form as a draft',
                'keywords' => 'draft form save form finish later incomplete form save progress continue form',
                'answer' => "While composing a form you can **save a draft** and finish later — your entries are kept until you submit.",
                'links' => $forms,
            ],
            [
                'category' => 'forms', 'title' => "Why can't I sign or edit a form?",
                'keywords' => 'cannot sign form cant edit form no sign button locked form not assigned why cant i sign disabled',
                'answer' => "You can only **sign or edit** a form when it is *in progress* **and** currently assigned to you. If the Sign button is missing, it's with someone else in the chain, or already completed/rejected — check its stage in the **Forms Portal**.",
                'links' => $portal,
            ],
            [
                'category' => 'forms', 'title' => 'Download a form as PDF',
                'keywords' => 'form pdf download form print form export form save form as pdf hard copy',
                'answer' => "Open any form from the **Forms Portal** and use its **PDF** option to download a clean, signed copy for printing or records.",
                'links' => $portal,
            ],
            [
                'category' => 'privacy', 'title' => 'Who can see my forms and memos?',
                'keywords' => 'who can see my form who sees my memo privacy visibility confidential can others read access to my form',
                'answer' => "A form is visible to its creator, its current handler, anyone who has signed a stage, and active members of the office handling it — no one else. Memos are visible to their sender and recipients. The system enforces this everywhere.",
                'links' => [],
            ],
            [
                'category' => 'memos', 'title' => 'Send an urgency alert on a memo',
                'keywords' => 'urgent memo urgency alert escalate memo remind recipient nudge chase memo priority',
                'answer' => "From a memo in the **Memos Portal** you can send an **urgency alert** to prompt the recipient when something is time-sensitive.",
                'links' => $memoPortal,
            ],
            [
                'category' => 'memos', 'title' => 'Archive old memos',
                'keywords' => 'archive memo clear memos tidy inbox archive completed remove old memos declutter',
                'answer' => "You can archive completed memos (individually or in bulk) from the **Memos Portal** to keep your list tidy; archived memos can be reactivated later.",
                'links' => $memoPortal,
            ],
            [
                'category' => 'memos', 'title' => 'Export a memo conversation as PDF',
                'keywords' => 'export memo pdf download memo thread print memo conversation save memo chat',
                'answer' => "Open the memo's chat thread in the **Memos Portal** and export it as a **PDF** for your records.",
                'links' => $memoPortal,
            ],
            [
                'category' => 'archive', 'title' => 'Open a password-protected folder',
                'keywords' => 'unlock folder folder password locked folder cant open folder enter folder password protected folder',
                'answer' => "If a folder is locked, open it from **My Folders** and enter its password when prompted. You also need access to the folder — if you don't have the password, ask the folder owner.",
                'links' => $myFolders,
            ],
            [
                'category' => 'archive', 'title' => 'Share a folder with a department, committee or office',
                'keywords' => 'share folder give access department committee office team share files grant folder access add members to folder',
                'answer' => "Open a folder in **My Folders** and use its sharing/members options to share it with a whole **department, committee or office** at once, so everyone in that group gets access.",
                'links' => $myFolders,
            ],
            [
                'category' => 'profile', 'title' => 'Update my profile details or picture',
                'keywords' => 'profile picture update profile change details photo avatar edit profile my information',
                'answer' => "Update your personal details from your **Profile**, and your password, e-signature, memo salutation and text size from **Settings**.",
                'links' => [['label' => 'Profile', 'url' => $this->u('dashboard.profile')], $settings[0]],
            ],
            [
                'category' => 'help', 'title' => 'Getting help / who to contact',
                'keywords' => 'help support contact who do i ask stuck problem report issue assistance it support',
                'answer' => "Start with the **System Documentation** and **User Manual** on your dashboard. *(Super Admin: edit this entry to add your institution's IT/support contact.)*",
                'links' => [['label' => 'System Documentation', 'url' => $this->u('dashboard.system-documentation')], ['label' => 'User Manual', 'url' => $this->u('dashboard.user-manual')]],
            ],
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
