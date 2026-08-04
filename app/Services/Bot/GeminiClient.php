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
    /**
     * Default cascade (overridable via the `bot_model_cascade` setting).
     * Only widely-available, stable model IDs — the Super Admin can add newer
     * ones (e.g. gemini-3-* shown by the "Test" button) via the cascade field.
     */
    public const DEFAULT_MODELS = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
    ];

    /** Model IDs that are no longer valid and must be scrubbed from a saved cascade. */
    public const RETIRED_MODELS = [
        'gemini-1.5-flash',
        'gemini-1.5-flash-8b',
        'gemini-1.5-pro',
        'gemini-pro',
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
     * Validate a raw key and discover the models it can use. Used by the Super
     * Admin "Test" button. Validating via ListModels (not generateContent) proves
     * the key works regardless of which specific model IDs are current, and lets
     * us tell the admin exactly which model IDs to put in the cascade.
     *
     * @return array{ok:bool, message:string, models:string[]}
     */
    public function testRawKey(string $plain): array
    {
        $plain = trim($plain);
        if ($plain === '') {
            return ['ok' => false, 'message' => 'Key is empty.', 'models' => []];
        }

        try {
            $response = Http::timeout(20)->acceptJson()
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$plain}&pageSize=200");
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Network error: ' . $e->getMessage(), 'models' => []];
        }

        if (!$response->successful()) {
            $msg = data_get($response->json(), 'error.message', 'HTTP ' . $response->status());
            return ['ok' => false, 'message' => $msg, 'models' => []];
        }

        // Keep only models that support text generation, strip the "models/" prefix.
        $models = collect($response->json('models', []))
            ->filter(fn ($m) => in_array('generateContent', (array) data_get($m, 'supportedGenerationMethods', []), true))
            ->map(fn ($m) => str_replace('models/', '', (string) data_get($m, 'name')))
            ->filter(fn ($n) => str_starts_with($n, 'gemini-'))
            ->values()
            ->all();

        return [
            'ok'      => true,
            'message' => 'Key is valid. ' . count($models) . ' usable text models found.',
            'models'  => $models,
        ];
    }

    /** @return string[] */
    private function models(): array
    {
        $configured = SystemSetting::get('bot_model_cascade');
        if (is_string($configured)) {
            $configured = json_decode($configured, true);
        }
        if (is_array($configured) && !empty($configured)) {
            $models = array_values(array_filter(
                array_map('trim', $configured),
                fn ($m) => $m !== '' && !in_array($m, self::RETIRED_MODELS, true),
            ));
            if (!empty($models)) {
                return $models;
            }
        }
        return self::DEFAULT_MODELS;
    }

    private function apiVersion(string $model): string
    {
        // All current Gemini models are served on v1beta.
        return 'v1beta';
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
                        'maxOutputTokens' => 4096,
                        'topP'            => 0.95,
                        'topK'            => 40,
                        // Disable "thinking" on 2.5/3.x flash models — otherwise thinking
                        // tokens can consume the whole output budget and return EMPTY text
                        // (which is exactly what made the first Test appear to "fail").
                        'thinkingConfig'  => ['thinkingBudget' => 0],
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ],
                ]);

            if ($response->successful()) {
                // Join every text part (skip "thought" parts), robust to responses
                // that split the answer across multiple parts.
                $parts = data_get($response->json(), 'candidates.0.content.parts', []);
                $text = collect($parts)
                    ->filter(fn ($p) => empty($p['thought']) && isset($p['text']))
                    ->map(fn ($p) => $p['text'])
                    ->implode('');

                if (trim($text) !== '') {
                    return $text;
                }

                // 200 but no usable text — surface why (e.g. MAX_TOKENS / SAFETY) so the
                // cascade moves on and the error is visible instead of a silent null.
                $reason = data_get($response->json(), 'candidates.0.finishReason', 'no text');
                throw new \RuntimeException("Gemini {$model} returned empty text (finishReason: {$reason})");
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
