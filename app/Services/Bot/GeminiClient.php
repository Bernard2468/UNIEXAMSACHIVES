<?php

namespace App\Services\Bot;

use App\Models\BotApiKey;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Non-streaming Gemini client, ported from GNRS's bright-handler edge function.
 *
 * Resilience strategy (same spirit as GNRS):
 *  - **Key pool** — tries each active key in {@see BotApiKey::pool()} in turn, so
 *    one exhausted key rolls over to the next. Per-key usage/failures are recorded.
 *  - **Model cascade** — for each key, tries each model (smartest → most reliable),
 *    each model having its own quota pool on Google's side.
 *  - **Retry** — transient 5xx/429/408 are retried with exponential backoff.
 *
 * Returns the full answer text, or null if every key/model failed (the caller
 * then degrades gracefully to a local answer).
 */
class GeminiClient
{
    /** Default cascade (overridable via the `bot_model_cascade` setting). */
    public const DEFAULT_MODELS = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-1.5-flash',
        'gemini-1.5-flash-8b',
    ];

    public function hasKeys(): bool
    {
        try {
            return BotApiKey::where('provider', 'gemini')->where('is_active', true)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Validate a raw key by making one tiny live call. Used by the Super Admin
     * "Test" button. Returns ['ok' => bool, 'message' => string, 'model' => ?string].
     */
    public function testRawKey(string $plain): array
    {
        $plain = trim($plain);
        if ($plain === '') {
            return ['ok' => false, 'message' => 'Key is empty.', 'model' => null];
        }

        $contents = [['role' => 'user', 'parts' => [['text' => 'Reply with the single word: OK']]]];

        foreach ($this->models() as $model) {
            try {
                $text = $this->callModel($plain, $model, $contents, 0.0);
                if ($text !== null) {
                    return ['ok' => true, 'message' => 'Connection successful.', 'model' => $model];
                }
            } catch (\Throwable $e) {
                // try the next model; remember the last error
                $last = $e->getMessage();
            }
        }

        return ['ok' => false, 'message' => $last ?? 'All models failed for this key.', 'model' => null];
    }

    /** @return string[] */
    private function models(): array
    {
        $configured = SystemSetting::get('bot_model_cascade');
        if (is_string($configured)) {
            $configured = json_decode($configured, true);
        }
        if (is_array($configured) && !empty($configured)) {
            return array_values(array_filter(array_map('trim', $configured)));
        }
        return self::DEFAULT_MODELS;
    }

    private function apiVersion(string $model): string
    {
        return str_starts_with($model, 'gemini-1') ? 'v1' : 'v1beta';
    }

    /**
     * Ask Gemini for a full (non-streamed) completion.
     *
     * @param string                                    $systemPrompt grounding prompt (system map + live context)
     * @param array<int,array{role:string,content:string}> $messages   ordered chat turns
     */
    public function chat(string $systemPrompt, array $messages, float $temperature = 0.6): ?string
    {
        $keys = BotApiKey::pool('gemini');
        if ($keys->isEmpty()) {
            return null;
        }

        $models   = $this->models();
        $contents = $this->buildContents($systemPrompt, $messages);

        foreach ($keys as $key) {
            $plain = $key->plainKey();
            if (!$plain) {
                continue;
            }

            foreach ($models as $model) {
                try {
                    $text = $this->callModel($plain, $model, $contents, $temperature);
                    if ($text !== null && trim($text) !== '') {
                        $key->markUsed(false);
                        return trim($text);
                    }
                } catch (\Throwable $e) {
                    Log::warning('[Bot] Gemini call failed', [
                        'model' => $model, 'error' => $e->getMessage(),
                    ]);
                    // try next model / next key
                }
            }

            // every model failed for this key
            $key->markUsed(true);
        }

        return null;
    }

    /**
     * Build Gemini `contents`. The system prompt is prepended to the first user
     * turn (Gemini has no dedicated system role on the generateContent endpoint).
     */
    private function buildContents(string $systemPrompt, array $messages): array
    {
        $contents = [];
        foreach ($messages as $m) {
            $role = ($m['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => (string) ($m['content'] ?? '')]]];
        }

        if (empty($contents)) {
            $contents[] = ['role' => 'user', 'parts' => [['text' => '']]];
        }

        // Prepend the grounding prompt to the first user turn.
        $firstUserIndex = null;
        foreach ($contents as $i => $c) {
            if ($c['role'] === 'user') {
                $firstUserIndex = $i;
                break;
            }
        }
        if ($firstUserIndex === null) {
            array_unshift($contents, ['role' => 'user', 'parts' => [['text' => '']]]);
            $firstUserIndex = 0;
        }
        $contents[$firstUserIndex]['parts'][0]['text'] =
            $systemPrompt . "\n\n---\n\n" . $contents[$firstUserIndex]['parts'][0]['text'];

        return $contents;
    }

    /** One model call with up to 3 attempts (retry transient errors). */
    private function callModel(string $apiKey, string $model, array $contents, float $temperature): ?string
    {
        $version = $this->apiVersion($model);
        $url = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key={$apiKey}";

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = Http::timeout(25)
                ->acceptJson()
                ->post($url, [
                    'contents'         => $contents,
                    'generationConfig' => [
                        'temperature'     => $temperature,
                        'maxOutputTokens' => 2048,
                        'topP'            => 0.95,
                        'topK'            => 40,
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ],
                ]);

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
                return is_string($text) ? $text : null;
            }

            $status = $response->status();

            // Fail fast on quota-gone / not-found / auth so we roll to the next model/key.
            if (in_array($status, [400, 401, 403, 404], true)) {
                throw new \RuntimeException("Gemini {$model} HTTP {$status}: " . $response->body());
            }
            if ($status === 429 && str_contains($response->body(), 'limit: 0')) {
                throw new \RuntimeException("Gemini {$model} daily quota exhausted");
            }

            // Retry transient errors.
            if (in_array($status, [408, 429, 500, 503], true) && $attempt < $maxAttempts) {
                usleep((int) (pow(2, $attempt) * 250_000)); // 0.5s, 1s
                continue;
            }

            throw new \RuntimeException("Gemini {$model} HTTP {$status}");
        }

        return null;
    }
}
