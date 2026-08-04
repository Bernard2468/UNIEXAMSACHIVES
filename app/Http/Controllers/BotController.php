<?php

namespace App\Http\Controllers;

use App\Models\BotConversation;
use App\Models\BotFeedback;
use App\Models\BotMessage;
use App\Models\BotUsageDaily;
use App\Models\SystemSetting;
use App\Services\Bot\BotBrain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * User-facing endpoints for the UDTS Assistant widget.
 *
 * `ask()` enforces the master switch and the per-user daily Gemini cap, delegates
 * to {@see BotBrain}, records privacy-safe usage counters, and returns the answer
 * plus how many messages the user has left. `feedback()` records 👍/👎 — storing
 * the actual question/answer only when the Super Admin has enabled transcripts.
 */
class BotController extends Controller
{
    /** A cap of 0 means "unlimited Gemini calls". */
    private function dailyCap(): int
    {
        return (int) SystemSetting::get('bot_daily_user_cap', 40);
    }

    private function botEnabled(): bool
    {
        return (bool) SystemSetting::get('bot_enabled', true);
    }

    private function transcriptsOn(): bool
    {
        return (bool) SystemSetting::get('bot_store_transcripts', false);
    }

    public function ask(Request $request, BotBrain $brain)
    {
        $data = $request->validate([
            'message'         => 'required|string|max:2000',
            'page'            => 'nullable|string|max:120',
            'history'         => 'nullable|array|max:20',
            'history.*.role'  => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
            'conversation_id' => 'nullable|integer',
        ]);

        if (!$this->botEnabled()) {
            return response()->json([
                'disabled' => true,
                'answer'   => 'The assistant is currently switched off.',
            ], 403);
        }

        $user = Auth::user();

        $cap        = $this->dailyCap();
        $unlimited  = $cap <= 0;
        $usedApi    = BotUsageDaily::apiCallsToday($user->id);
        $allowApi   = $unlimited || $usedApi < $cap;

        $startedAt = microtime(true);
        $result = $brain->answer(
            $user,
            $data['message'],
            $data['history'] ?? [],
            $data['page'] ?? null,
            $allowApi,
        );
        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        // Privacy-safe usage counters (no content).
        try {
            BotUsageDaily::todayFor($user->id)->record($result['source'], $latencyMs);
        } catch (\Throwable $e) {
            // never let analytics break the answer
        }

        // Optional transcript storage — OFF by default.
        $conversationId = $data['conversation_id'] ?? null;
        if ($this->transcriptsOn()) {
            $conversationId = $this->storeTranscript(
                $user->id,
                $data['page'] ?? null,
                $conversationId,
                $data['message'],
                $result['answer'],
                $result['source'],
            );
        }

        $usedAfter = $unlimited ? 0 : BotUsageDaily::apiCallsToday($user->id);
        $remaining = $unlimited ? null : max(0, $cap - $usedAfter);

        return response()->json([
            'answer'          => $result['answer'],
            'source'          => $result['source'],
            'matched_key'     => $result['matched_key'],
            'remaining'       => $remaining,
            'limit'           => $unlimited ? null : $cap,
            'unlimited'       => $unlimited,
            'conversation_id' => $conversationId,
        ]);
    }

    public function feedback(Request $request)
    {
        $data = $request->validate([
            'rating'      => 'required|in:up,down',
            'source'      => 'nullable|string|max:16',
            'matched_key' => 'nullable|string|max:120',
            'question'    => 'nullable|string|max:2000',
            'answer'      => 'nullable|string|max:8000',
        ]);

        if (!$this->botEnabled()) {
            return response()->json(['ok' => false], 403);
        }

        $store = $this->transcriptsOn();

        try {
            BotFeedback::create([
                'user_id'     => Auth::id(),
                'rating'      => $data['rating'],
                'source'      => $data['source'] ?? null,
                'matched_key' => $data['matched_key'] ?? null,
                // content only when transcripts are explicitly enabled
                'question'    => $store ? ($data['question'] ?? null) : null,
                'answer'      => $store ? ($data['answer'] ?? null) : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false]);
        }

        return response()->json(['ok' => true]);
    }

    private function storeTranscript(int $userId, ?string $page, ?int $conversationId, string $question, string $answer, string $source): ?int
    {
        try {
            $conversation = $conversationId
                ? BotConversation::where('id', $conversationId)->where('user_id', $userId)->first()
                : null;

            if (!$conversation) {
                $conversation = BotConversation::create(['user_id' => $userId, 'page' => $page]);
            }

            BotMessage::create([
                'conversation_id' => $conversation->id,
                'role'            => 'user',
                'content'         => $question,
                'source'          => null,
            ]);
            BotMessage::create([
                'conversation_id' => $conversation->id,
                'role'            => 'assistant',
                'content'         => $answer,
                'source'          => $source,
            ]);

            return $conversation->id;
        } catch (\Throwable $e) {
            return $conversationId;
        }
    }
}
