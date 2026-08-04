<?php

namespace App\Services\Bot;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Orchestrates a single bot answer through the local-first cascade:
 *
 *   1. LiveDataResolver  → the user's own live data (0 API)
 *   2. KnowledgeBase     → built-in + editable how-to/FAQ (0 API)
 *   3. Response cache    → a previously-computed Gemini answer (0 API)
 *   4. GeminiClient      → only when allowed, keyed, and the local layers missed
 *
 * The return shape is stable for the controller/UI:
 *   ['answer' => string, 'source' => live|kb|cache|api|fallback,
 *    'links' => [{label,url}], 'matched_key' => string]
 *
 * Only `source === 'api'` consumes a paid call / the user's daily cap.
 */
class BotBrain
{
    public function __construct(
        private LiveDataResolver $live,
        private KnowledgeBase $kb,
        private GeminiClient $gemini,
    ) {}

    /** Cache Gemini answers for a day so identical questions are free. */
    private const CACHE_TTL = 86400;

    /**
     * @param array<int,array{role:string,content:string}> $history prior turns (oldest→newest)
     */
    public function answer(User $user, string $message, array $history, ?string $page, bool $allowApi): array
    {
        $message = trim($message);

        // 1) Live data about the user / institution.
        if ($hit = $this->live->resolve($message, $user)) {
            return $this->shape($hit['answer'], 'live', $hit['links'], $hit['matched_key']);
        }

        // 2) Knowledge base (how-to, FAQ, small-talk).
        if ($hit = $this->kb->search($message)) {
            return $this->shape($hit['answer'], 'kb', $hit['links'], $hit['matched_key']);
        }

        // 3) Cached Gemini answer for this exact question.
        $cacheKey = 'bot_cache:' . sha1(mb_strtolower($message));
        if ($cached = Cache::get($cacheKey)) {
            return $this->shape($cached, 'cache', [], 'cache');
        }

        // 4) Gemini — only if permitted, configured, and reachable.
        if ($allowApi && $this->gemini->hasKeys()) {
            $system   = $this->buildSystemPrompt($user, $page);
            $messages = $this->buildMessages($history, $message);
            $answer   = $this->gemini->chat($system, $messages);

            if ($answer !== null && trim($answer) !== '') {
                Cache::put($cacheKey, $answer, self::CACHE_TTL);
                return $this->shape($answer, 'api', [], 'api');
            }
        }

        // 5) Graceful local fallback — still useful, no dead ends.
        return $this->fallback($allowApi);
    }

    private function buildSystemPrompt(User $user, ?string $page): string
    {
        $prompt = $this->kb->systemMap();

        // Personalise by name only. Role labels are deliberately omitted — this
        // system uses reversed role terminology, so surfacing a raw role could
        // mislead both the user and the model.
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'there';
        $prompt .= "\n\n## Current user\nYou are speaking with {$name}.";

        if ($page) {
            $prompt .= " They are currently on the **{$page}** page — use this for more relevant answers.";
        }

        return $prompt;
    }

    /**
     * @param array<int,array{role:string,content:string}> $history
     * @return array<int,array{role:string,content:string}>
     */
    private function buildMessages(array $history, string $message): array
    {
        // Keep the last 6 turns to bound token use.
        $trimmed = array_slice($history, -6);

        $messages = [];
        foreach ($trimmed as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }

    private function fallback(bool $allowApi): array
    {
        $why = $allowApi
            ? "I don't have a ready answer for that one yet"
            : "I can't reach my deep-thinking engine right now, but I'm still here to help";

        $answer = "{$why}. I can help you with **forms**, **memos**, the **files & exams archive**, "
            . "your **subscription**, or **settings** — try asking about one of those, or use the links below.";

        return $this->shape($answer, 'fallback', [
            ['label' => 'Forms Portal', 'url' => $this->safe('admin.forms.portal')],
            ['label' => 'Memos', 'url' => $this->safe('dashboard.uimms.portal')],
            ['label' => 'Files', 'url' => $this->safe('dashboard.all.files')],
        ], 'fallback');
    }

    private function safe(string $route): string
    {
        try {
            return route($route, [], false);
        } catch (\Throwable $e) {
            return '/';
        }
    }

    private function shape(string $answer, string $source, array $links, string $matchedKey): array
    {
        // Fallback links are appended as markdown so the widget renders them inline.
        if (!empty($links) && $source === 'fallback') {
            $line = collect($links)->map(fn ($l) => "[{$l['label']}]({$l['url']})")->implode(' · ');
            $answer .= "\n\n" . $line;
        }

        return [
            'answer'      => $answer,
            'source'      => $source,
            'links'       => $links,
            'matched_key' => $matchedKey,
        ];
    }
}
