<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BotConversation;
use App\Services\Support\SupportChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * User-facing endpoints for the Support Chat (bot → human handoff) that lives
 * inside the MetaGuide widget. Every write goes through {@see SupportChatService};
 * every read is authorized by the SupportConversationPolicy.
 */
class SupportChatController extends Controller
{
    public function __construct(private SupportChatService $support)
    {
    }

    /** Escalate from the bot to a human (or resume the user's open thread). */
    public function escalate(Request $request)
    {
        if (!$this->support->isEnabled()) {
            return response()->json(['enabled' => false], 403);
        }

        $data = $request->validate([
            'message'             => 'nullable|string|max:4000',
            'category'            => 'nullable|string|max:40',
            'subject'             => 'nullable|string|max:180',
            'page'                => 'nullable|string|max:120',
            'client_id'           => 'nullable|string|max:64',
            'bot_context'         => 'nullable|array|max:20',
            'bot_context.*.role'  => 'required_with:bot_context|string|in:user,assistant',
            'bot_context.*.content' => 'required_with:bot_context|string|max:4000',
        ]);

        $conversation = $this->support->startOrResumeSupport(Auth::user(), $data);

        return response()->json($this->threadPayload($conversation, markRead: true));
    }

    /** The user's current open support thread, if any (for widget resume). */
    public function active(Request $request)
    {
        $conversation = BotConversation::support()
            ->where('user_id', Auth::id())
            ->orderByRaw("CASE WHEN status != 'resolved' THEN 0 ELSE 1 END")
            ->latest('id')
            ->first();

        if (!$conversation) {
            return response()->json(['conversation' => null]);
        }

        return response()->json($this->threadPayload($conversation, markRead: false));
    }

    /** Poll a thread for new messages. */
    public function thread(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('view', $conversation), 403);

        $after = (int) $request->query('after', 0);

        if ($after === 0) {
            $this->support->markReadByUser($conversation);
        }

        return response()->json($this->threadPayload($conversation->fresh(), markRead: false, after: $after));
    }

    /** Send a message as the requesting user. */
    public function message(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('reply', $conversation), 403);

        $data = $request->validate([
            'body'      => 'required|string|max:4000',
            'client_id' => 'nullable|string|max:64',
        ]);

        $msg = $this->support->postUserMessage($conversation, Auth::user(), $data['body'], $data['client_id'] ?? null);

        return response()->json([
            'ok'      => true,
            'message' => $msg ? $this->support->serializeMessage($msg) : null,
        ]);
    }

    /** The requester marks their own conversation resolved. */
    public function resolve(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('resolve', $conversation), 403);

        $this->support->resolve($conversation, Auth::user());

        return response()->json(['ok' => true, 'conversation' => $this->support->serializeConversation($conversation->fresh())]);
    }

    /** Shared payload builder: conversation meta + user-visible messages. */
    private function threadPayload(BotConversation $conversation, bool $markRead, int $after = 0): array
    {
        if ($markRead) {
            $this->support->markReadByUser($conversation);
            $conversation = $conversation->fresh();
        }

        $query = $conversation->messages()
            ->where('is_internal', false)
            ->orderBy('id');

        if ($after > 0) {
            $query->where('id', '>', $after);
        }

        $messages = $query->with('sender')->get()
            ->map(fn ($m) => $this->support->serializeMessage($m))
            ->values();

        return [
            'conversation' => $this->support->serializeConversation($conversation),
            'messages'     => $messages,
            'server_time'  => now()->toIso8601String(),
        ];
    }
}
