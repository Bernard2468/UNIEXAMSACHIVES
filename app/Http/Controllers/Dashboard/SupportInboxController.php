<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BotConversation;
use App\Services\Support\SupportChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The shared Support Inbox for agents (Support office + Institutional Admins +
 * Super Admins). Gated at the route level by the `support_agent` middleware;
 * per-conversation reads/writes are still checked against the policy.
 *
 * Reads are polled on the same cheap `after`-id basis as the UIMMS memo chat —
 * the app's proven real-time pattern on shared hosting (no WebSockets).
 */
class SupportInboxController extends Controller
{
    public function __construct(private SupportChatService $support)
    {
    }

    public function index(Request $request)
    {
        // The agent is here → the desk is staffed. Mark presence before reading it.
        $this->support->touchAgentPresence();

        return view('admin.support.inbox', [
            'preselect' => (int) $request->query('c', 0),
            'hoursText' => $this->support->hoursText(),
            'online'    => $this->support->isSupportOnline(),
        ]);
    }

    /** Conversation list for the inbox rail (filterable + searchable). */
    public function list(Request $request)
    {
        $this->support->touchAgentPresence(); // keep the desk marked online while polling

        $filter = $request->query('filter', 'open');
        $search = trim((string) $request->query('q', ''));
        $me = Auth::id();

        $query = BotConversation::support()
            ->with(['user', 'assignedAgent', 'latestMessage'])
            ->orderByDesc('updated_at');

        match ($filter) {
            'mine'       => $query->open()->where('assigned_agent_id', $me),
            'unassigned' => $query->open()->whereNull('assigned_agent_id'),
            'resolved'   => $query->where('status', BotConversation::STATUS_RESOLVED),
            'all'        => $query,
            default      => $query->open(),
        };

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $rows = $query->limit(150)->get()->map(function (BotConversation $c) {
            $owner = $c->user;
            $agent = $c->assignedAgent;
            $last = $c->latestMessage;

            return [
                'id'          => $c->id,
                'subject'     => $c->subject ?: 'Support request',
                'category'    => $c->category,
                'status'      => $c->status,
                'resolved'    => $c->isResolved(),
                'unread'      => (int) $c->agent_unread,
                'user_name'   => $owner ? (trim($owner->first_name . ' ' . $owner->last_name) ?: 'User') : 'User',
                'user_avatar' => $owner?->profile_picture_url,
                'assigned_agent_id' => $c->assigned_agent_id,
                'agent_name'  => $agent ? (trim($agent->first_name . ' ' . $agent->last_name) ?: 'Agent') : null,
                'snippet'     => $last ? Str::limit($this->snippetPrefix($last) . $last->content, 90) : '',
                'time_h'      => optional($c->updated_at)->diffForHumans(null, true),
                'waiting_min' => $c->status === BotConversation::STATUS_QUEUED
                    ? (int) optional($c->created_at)->diffInMinutes(now())
                    : null,
            ];
        })->values();

        return response()->json([
            'conversations' => $rows,
            'counts'        => $this->badgeCounts(),
            'server_time'   => now()->toIso8601String(),
        ]);
    }

    /** Full thread (including internal notes + the carried bot context). */
    public function show(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('view', $conversation), 403);

        $this->support->markReadByAgent($conversation);

        return response()->json($this->threadPayload($conversation->fresh()));
    }

    /** Poll a thread for new messages. */
    public function messages(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('view', $conversation), 403);

        $after = (int) $request->query('after', 0);
        if ($after === 0) {
            $this->support->markReadByAgent($conversation);
        }

        return response()->json($this->threadPayload($conversation->fresh(), $after));
    }

    public function reply(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('reply', $conversation), 403);

        $data = $request->validate([
            'body'      => 'required|string|max:4000',
            'internal'  => 'nullable|boolean',
            'client_id' => 'nullable|string|max:64',
        ]);

        $msg = $this->support->postAgentMessage(
            $conversation,
            Auth::user(),
            $data['body'],
            (bool) ($data['internal'] ?? false),
            $data['client_id'] ?? null,
        );

        return response()->json([
            'ok'      => true,
            'message' => $msg ? $this->support->serializeMessage($msg) : null,
        ]);
    }

    public function claim(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('manage', $conversation), 403);
        $this->support->claim($conversation, Auth::user());
        return response()->json(['ok' => true]);
    }

    public function resolve(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('resolve', $conversation), 403);
        $this->support->resolve($conversation, Auth::user());
        return response()->json(['ok' => true]);
    }

    public function reopen(Request $request, BotConversation $conversation)
    {
        abort_unless(Auth::user()->can('manage', $conversation), 403);
        $this->support->reopen($conversation, Auth::user());
        return response()->json(['ok' => true]);
    }

    /** Badge counts for the sidebar / inbox header. */
    public function counts(Request $request)
    {
        $this->support->touchAgentPresence();
        return response()->json($this->badgeCounts());
    }

    // ===== Internals =====

    private function badgeCounts(): array
    {
        $me = Auth::id();
        return [
            'open'       => BotConversation::support()->open()->count(),
            'unassigned' => BotConversation::support()->open()->whereNull('assigned_agent_id')->count(),
            'mine'       => BotConversation::support()->open()->where('assigned_agent_id', $me)->count(),
        ];
    }

    private function snippetPrefix(\App\Models\BotMessage $m): string
    {
        return match ($m->sender_type) {
            'agent'  => 'You: ',
            'system' => '',
            default  => '',
        };
    }

    private function threadPayload(BotConversation $conversation, int $after = 0): array
    {
        $query = $conversation->messages()->orderBy('id');
        if ($after > 0) {
            $query->where('id', '>', $after);
        }

        $messages = $query->with('sender')->get()
            ->map(fn ($m) => $this->support->serializeMessage($m))
            ->values();

        return [
            'conversation' => $this->support->serializeConversation($conversation, forAgent: true),
            'messages'     => $messages,
            'server_time'  => now()->toIso8601String(),
        ];
    }
}
