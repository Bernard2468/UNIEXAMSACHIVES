<?php

namespace App\Services\Support;

use App\Mail\SupportReplyToUser;
use App\Mail\SupportTicketReceived;
use App\Models\BotConversation;
use App\Models\BotMessage;
use App\Models\Notification;
use App\Models\Office;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Single mutator for the human Support Chat (bot → human handoff).
 *
 * Every state transition on a support {@see BotConversation} runs here, inside a
 * DB transaction — controllers never touch the model directly. This mirrors the
 * Forms {@see \App\Services\Forms\FormWorkflowService} boundary and keeps the
 * conversation's counters, status machine, notifications and emails consistent.
 *
 * Notifications are always written in-app (cheap, already polled by the header
 * tray + widget). Emails are best-effort and wrapped in try/catch so an SMTP
 * outage can never abort a reply — the same discipline the form mails use.
 */
class SupportChatService
{
    // ===== Configuration (Super-Admin tunable via SystemSetting) =====

    public function isEnabled(): bool
    {
        return (bool) SystemSetting::get('support_chat_enabled', true);
    }

    public function officeSlug(): string
    {
        return (string) SystemSetting::get('support_office_slug', 'it-support');
    }

    public function supportOffice(): ?Office
    {
        try {
            return Office::where('slug', $this->officeSlug())->where('is_active', true)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Working days as ISO weekday numbers (1 = Mon … 7 = Sun). */
    private function workingDays(): array
    {
        $days = SystemSetting::get('support_days', [1, 2, 3, 4, 5]);
        if (is_string($days)) {
            $days = array_filter(array_map('intval', explode(',', $days)));
        }
        return is_array($days) && $days ? array_map('intval', $days) : [1, 2, 3, 4, 5];
    }

    public function hoursStart(): string
    {
        return (string) SystemSetting::get('support_hours_start', '08:00');
    }

    public function hoursEnd(): string
    {
        return (string) SystemSetting::get('support_hours_end', '17:00');
    }

    /** True when a live agent is expected to be around (within staffed hours). */
    public function isWithinSupportHours(): bool
    {
        $now = now();
        if (!in_array($now->dayOfWeekIso, $this->workingDays(), true)) {
            return false;
        }
        try {
            $start = $now->copy()->setTimeFromTimeString($this->hoursStart());
            $end   = $now->copy()->setTimeFromTimeString($this->hoursEnd());
        } catch (\Throwable $e) {
            return true; // never block the handoff on a misconfigured time
        }
        return $now->between($start, $end);
    }

    /** Human-readable support hours, e.g. "Mon–Fri, 8:00 AM – 5:00 PM". */
    public function hoursText(): string
    {
        $labels = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $days = $this->workingDays();
        sort($days);
        $dayText = count($days) > 1 && $days === range($days[0], $days[count($days) - 1])
            ? ($labels[$days[0]] ?? '?') . '–' . ($labels[end($days)] ?? '?')
            : implode(', ', array_map(fn ($d) => $labels[$d] ?? '?', $days));

        $fmt = function (string $hm): string {
            try {
                return now()->setTimeFromTimeString($hm)->format('g:i A');
            } catch (\Throwable $e) {
                return $hm;
            }
        };

        return "{$dayText}, {$fmt($this->hoursStart())} – {$fmt($this->hoursEnd())}";
    }

    // ===== Live presence =====
    //
    // "Online" reflects whether an agent is ACTUALLY here, not just a static
    // schedule — so an agent viewing the inbox always reads as online, and users
    // only see "online" when someone is really watching the queue. A single
    // cache key (driver-agnostic) is refreshed by the inbox's own polling.

    private const PRESENCE_KEY    = 'support:last_agent_activity';
    private const PRESENCE_WINDOW = 300; // seconds an agent still counts as "present"

    /** Called by the agent inbox (page load + each poll) to mark the desk staffed. */
    public function touchAgentPresence(): void
    {
        try {
            Cache::put(self::PRESENCE_KEY, now()->getTimestamp(), 900);
        } catch (\Throwable $e) {
            // presence is best-effort — never let it break a request
        }
    }

    /** True when a support agent has been active within the presence window. */
    public function agentPresent(): bool
    {
        try {
            $ts = Cache::get(self::PRESENCE_KEY);
            return $ts && (now()->getTimestamp() - (int) $ts) <= self::PRESENCE_WINDOW;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * The status users and agents actually see. An agent physically present wins;
     * otherwise we fall back to the staffed schedule so it still reads "online"
     * during normal hours before anyone has opened the inbox that day.
     */
    public function isSupportOnline(): bool
    {
        return $this->agentPresent() || $this->isWithinSupportHours();
    }

    // ===== Agent pool =====

    /**
     * Who to alert about support activity. The Support office (if it has active
     * members) is the first responder; otherwise the whole admin pool. Kept small
     * so email volume stays sane.
     *
     * @return Collection<int, User>
     */
    public function agentRecipients(): Collection
    {
        $office = $this->supportOffice();
        if ($office) {
            $members = $office->activeUsers()->get();
            if ($members->isNotEmpty()) {
                return $members;
            }
        }

        // Fallback: Super Admins + Institutional Admins (UI "Admin", is_admin = false).
        return User::query()
            ->where(fn ($q) => $q->where('role', 'super_admin')->orWhere('is_admin', false))
            ->get();
    }

    public function isAgent(User $user): bool
    {
        return $user->isSupportAgent();
    }

    // ===== Escalation (bot → human) =====

    /**
     * Start a new support thread — or resume the user's existing open one so a
     * person never ends up with two parallel tickets.
     *
     * @param array{message?:string,category?:string,subject?:string,page?:string,bot_context?:array,client_id?:string} $data
     */
    public function startOrResumeSupport(User $user, array $data): BotConversation
    {
        return DB::transaction(function () use ($user, $data) {
            $existing = BotConversation::support()->open()
                ->where('user_id', $user->id)
                ->latest('id')
                ->first();

            if ($existing) {
                if (!empty($data['message'])) {
                    $msg = $this->appendUserMessage($existing, $user, $data['message'], $data['client_id'] ?? null);
                    if ($msg) {
                        $this->notifyAgentsOfReply($existing->fresh(), $user);
                    }
                }
                return $existing->fresh();
            }

            $office = $this->supportOffice();
            $online = $this->isSupportOnline();

            $conv = BotConversation::create([
                'user_id'      => $user->id,
                'page'         => $data['page'] ?? null,
                'mode'         => BotConversation::MODE_SUPPORT,
                'status'       => BotConversation::STATUS_QUEUED,
                'subject'      => $this->deriveSubject($data),
                'category'     => $data['category'] ?? null,
                'office_id'    => $office?->id,
                'user_unread'  => 0,
                'agent_unread' => 0,
                'meta'         => [
                    'bot_context'    => $this->sanitizeContext($data['bot_context'] ?? []),
                    'online_at_start' => $online,
                ],
            ]);

            $this->addSystemMessage(
                $conv,
                $online
                    ? 'You have been connected to the support desk. An administrator will be with you shortly.'
                    : 'Support is currently offline. Your request has been logged — an administrator will reply by email as soon as we are back online.'
            );

            if (!empty($data['message'])) {
                $this->appendUserMessage($conv, $user, $data['message'], $data['client_id'] ?? null);
            } else {
                $conv->forceFill(['last_user_message_at' => now()])->save();
            }

            $this->notifyAgentsOfNewTicket($conv->fresh(), $user, $online);

            return $conv->fresh();
        });
    }

    // ===== Messaging =====

    /** A message from the requesting user. Idempotent on client_id. */
    public function postUserMessage(BotConversation $conv, User $user, string $body, ?string $clientId = null): ?BotMessage
    {
        return DB::transaction(function () use ($conv, $user, $body, $clientId) {
            $msg = $this->appendUserMessage($conv, $user, $body, $clientId);
            if ($msg) {
                $this->notifyAgentsOfReply($conv->fresh(), $user);
            }
            return $msg;
        });
    }

    /** A reply (or internal note) from an agent. Auto-claims an unassigned thread. */
    public function postAgentMessage(BotConversation $conv, User $agent, string $body, bool $internal = false, ?string $clientId = null): ?BotMessage
    {
        return DB::transaction(function () use ($conv, $agent, $body, $internal, $clientId) {
            if ($clientId) {
                $dupe = $conv->messages()->where('client_id', $clientId)->first();
                if ($dupe) {
                    return $dupe;
                }
            }

            $msg = BotMessage::create([
                'conversation_id' => $conv->id,
                'role'            => 'assistant',
                'sender_type'     => BotMessage::SENDER_AGENT,
                'sender_id'       => $agent->id,
                'content'         => $body,
                'client_id'       => $clientId,
                'is_internal'     => $internal,
            ]);

            if ($internal) {
                return $msg; // notes are agent-private — no status/counter/notify changes
            }

            $justClaimed = false;
            if (!$conv->assigned_agent_id) {
                $conv->assigned_agent_id = $agent->id;
                $justClaimed = true;
            }
            $conv->status = BotConversation::STATUS_ACTIVE;
            $conv->resolved_at = null;
            $conv->resolved_by = null;
            $conv->last_agent_message_at = now();
            $conv->user_unread = $conv->user_unread + 1;
            $conv->agent_unread = 0;
            $conv->save();

            if ($justClaimed) {
                $this->addSystemMessage($conv, trim($agent->first_name . ' ' . $agent->last_name) . ' joined the conversation.');
            }

            $this->notifyUserOfReply($conv->fresh(), $agent);

            return $msg;
        });
    }

    // ===== Status transitions =====

    public function claim(BotConversation $conv, User $agent): void
    {
        DB::transaction(function () use ($conv, $agent) {
            if ((int) $conv->assigned_agent_id === (int) $agent->id) {
                return;
            }
            $conv->assigned_agent_id = $agent->id;
            if ($conv->status === BotConversation::STATUS_QUEUED) {
                $conv->status = BotConversation::STATUS_ACTIVE;
            }
            $conv->save();
            $this->addSystemMessage($conv, trim($agent->first_name . ' ' . $agent->last_name) . ' picked up this conversation.');
        });
    }

    public function resolve(BotConversation $conv, User $actor): void
    {
        DB::transaction(function () use ($conv, $actor) {
            $conv->status = BotConversation::STATUS_RESOLVED;
            $conv->resolved_at = now();
            $conv->resolved_by = $actor->id;

            $byAgent = (int) $actor->id !== (int) $conv->user_id;
            if ($byAgent) {
                $conv->user_unread = $conv->user_unread + 1;
            }
            $conv->save();

            $this->addSystemMessage($conv, 'Conversation marked as resolved.');

            if ($byAgent) {
                Notification::createSupportNotification(
                    $conv->user_id,
                    'Support request resolved',
                    'Your support conversation "' . Str::limit($conv->subject ?: 'Support request', 60) . '" was marked resolved.',
                    route('dashboard') . '?support=' . $conv->id,
                    $actor->id,
                    ['conversation_id' => $conv->id]
                );
            }
        });
    }

    public function reopen(BotConversation $conv, User $actor): void
    {
        DB::transaction(function () use ($conv, $actor) {
            $conv->status = $conv->assigned_agent_id
                ? BotConversation::STATUS_ACTIVE
                : BotConversation::STATUS_QUEUED;
            $conv->resolved_at = null;
            $conv->resolved_by = null;
            $conv->save();
            $this->addSystemMessage($conv, 'Conversation reopened.');
        });
    }

    // ===== Read receipts =====

    public function markReadByUser(BotConversation $conv): void
    {
        $conv->messages()
            ->whereIn('sender_type', [BotMessage::SENDER_AGENT, BotMessage::SENDER_SYSTEM])
            ->where('is_internal', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        if ($conv->user_unread !== 0) {
            $conv->forceFill(['user_unread' => 0])->save();
        }
    }

    public function markReadByAgent(BotConversation $conv): void
    {
        $conv->messages()
            ->where('sender_type', BotMessage::SENDER_USER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        if ($conv->agent_unread !== 0) {
            $conv->forceFill(['agent_unread' => 0])->save();
        }
    }

    // ===== Internal writes =====

    private function appendUserMessage(BotConversation $conv, User $user, string $body, ?string $clientId): ?BotMessage
    {
        if ($clientId) {
            $dupe = $conv->messages()->where('client_id', $clientId)->first();
            if ($dupe) {
                return $dupe;
            }
        }

        $msg = BotMessage::create([
            'conversation_id' => $conv->id,
            'role'            => 'user',
            'sender_type'     => BotMessage::SENDER_USER,
            'sender_id'       => $user->id,
            'content'         => $body,
            'client_id'       => $clientId,
            'is_internal'     => false,
        ]);

        $reopened = false;
        if ($conv->isResolved()) {
            $conv->status = $conv->assigned_agent_id
                ? BotConversation::STATUS_ACTIVE
                : BotConversation::STATUS_QUEUED;
            $conv->resolved_at = null;
            $conv->resolved_by = null;
            $reopened = true;
        }
        $conv->last_user_message_at = now();
        $conv->agent_unread = $conv->agent_unread + 1;
        $conv->user_unread = 0;
        $conv->save();

        if ($reopened) {
            $this->addSystemMessage($conv, 'Conversation reopened by the requester.');
        }

        return $msg;
    }

    private function addSystemMessage(BotConversation $conv, string $text): BotMessage
    {
        return BotMessage::create([
            'conversation_id' => $conv->id,
            'role'            => 'assistant',
            'sender_type'     => BotMessage::SENDER_SYSTEM,
            'sender_id'       => null,
            'content'         => $text,
            'is_internal'     => false,
        ]);
    }

    // ===== Notifications / email =====

    private function notifyAgentsOfNewTicket(BotConversation $conv, User $user, bool $online): void
    {
        $name = trim($user->first_name . ' ' . $user->last_name) ?: 'A user';
        $url  = route('dashboard.support.inbox') . '?c=' . $conv->id;
        $recipients = $this->agentRecipients();

        foreach ($recipients as $agent) {
            try {
                Notification::createSupportNotification(
                    $agent->id,
                    $online ? 'New support chat' : 'New support ticket (offline)',
                    "{$name} needs help: " . Str::limit($conv->subject ?: 'Support request', 70),
                    $url,
                    $user->id,
                    ['conversation_id' => $conv->id]
                );
            } catch (\Throwable $e) {
                // in-app notification failure must never break the handoff
            }
        }

        // Email is best-effort and capped to keep inboxes sane.
        foreach ($recipients->take(25) as $agent) {
            if (empty($agent->email)) {
                continue;
            }
            try {
                Mail::to($agent->email)->send(new SupportTicketReceived($conv, $user, $agent, $online));
            } catch (\Throwable $e) {
                // swallow — the in-app alert already landed
            }
        }
    }

    private function notifyAgentsOfReply(BotConversation $conv, User $user): void
    {
        $name = trim($user->first_name . ' ' . $user->last_name) ?: 'A user';
        $url  = route('dashboard.support.inbox') . '?c=' . $conv->id;

        // A claimed thread pings only its owner-agent; an unclaimed one pings the pool.
        $recipients = $conv->assigned_agent_id
            ? User::where('id', $conv->assigned_agent_id)->get()
            : $this->agentRecipients();

        foreach ($recipients as $agent) {
            if ((int) $agent->id === (int) $user->id) {
                continue;
            }
            try {
                Notification::createSupportNotification(
                    $agent->id,
                    'New reply in support chat',
                    "{$name}: " . Str::limit($conv->subject ?: 'Support request', 70),
                    $url,
                    $user->id,
                    ['conversation_id' => $conv->id]
                );
            } catch (\Throwable $e) {
            }
        }
    }

    private function notifyUserOfReply(BotConversation $conv, User $agent): void
    {
        try {
            Notification::createSupportNotification(
                $conv->user_id,
                'Support replied to you',
                'An administrator responded to your support request.',
                route('dashboard') . '?support=' . $conv->id,
                $agent->id,
                ['conversation_id' => $conv->id]
            );
        } catch (\Throwable $e) {
        }

        // Email the requester on the FIRST agent reply of a session, so they hear
        // back even if they've navigated away. Guarded by a meta flag to avoid
        // emailing on every subsequent reply.
        $meta = $conv->meta ?? [];
        if (empty($meta['owner_emailed_reply'])) {
            $owner = $conv->user;
            if ($owner && !empty($owner->email)) {
                try {
                    Mail::to($owner->email)->send(new SupportReplyToUser($conv, $owner, $agent));
                    $meta['owner_emailed_reply'] = true;
                    $conv->forceFill(['meta' => $meta])->save();
                } catch (\Throwable $e) {
                }
            }
        }
    }

    // ===== Helpers =====

    private function deriveSubject(array $data): string
    {
        if (!empty($data['subject'])) {
            return Str::limit(trim($data['subject']), 180, '');
        }
        if (!empty($data['message'])) {
            return Str::limit(trim($data['message']), 90, '…');
        }
        if (!empty($data['category'])) {
            return ucfirst($data['category']) . ' help';
        }
        return 'Support request';
    }

    /**
     * Trim the bot transcript we carry into the handoff so the agent sees what
     * was already tried, without storing an unbounded blob.
     */
    private function sanitizeContext(array $context): array
    {
        $out = [];
        foreach (array_slice($context, -12) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content !== '') {
                $out[] = ['role' => $role, 'content' => Str::limit($content, 1500, '…')];
            }
        }
        return $out;
    }

    // ===== Serialization for JSON / polling =====

    /** @return array<string,mixed> */
    public function serializeMessage(BotMessage $m): array
    {
        $sender = $m->sender_type ?: ($m->role === 'user' ? 'user' : 'bot');
        $name = null;
        $avatar = null;
        if ($sender === BotMessage::SENDER_AGENT && $m->sender) {
            $name = trim($m->sender->first_name . ' ' . $m->sender->last_name) ?: 'Support';
            $avatar = $m->sender->profile_picture_url;
        } elseif ($sender === BotMessage::SENDER_USER && $m->sender) {
            $name = trim($m->sender->first_name . ' ' . $m->sender->last_name);
        }

        return [
            'id'          => $m->id,
            'sender'      => $sender,
            'body'        => (string) $m->content,
            'is_internal' => (bool) $m->is_internal,
            'name'        => $name,
            'avatar'      => $avatar,
            'time'        => optional($m->created_at)->toIso8601String(),
            'time_h'      => optional($m->created_at)->format('g:i A'),
        ];
    }

    /** @return array<string,mixed> */
    public function serializeConversation(BotConversation $conv, bool $forAgent = false): array
    {
        $agent = $conv->assignedAgent;
        $data = [
            'id'          => $conv->id,
            'status'      => $conv->status,
            'subject'     => $conv->subject,
            'category'    => $conv->category,
            'resolved'    => $conv->isResolved(),
            'agent_name'  => $agent ? (trim($agent->first_name . ' ' . $agent->last_name) ?: 'Support') : null,
            'agent_avatar' => $agent?->profile_picture_url,
            'online'      => $this->isSupportOnline(),
            'hours'       => $this->hoursText(),
            'updated'     => optional($conv->updated_at)->toIso8601String(),
        ];

        if ($forAgent) {
            $owner = $conv->user;
            $data['user'] = [
                'id'     => $conv->user_id,
                'name'   => $owner ? trim($owner->first_name . ' ' . $owner->last_name) : 'User',
                'email'  => $owner?->email,
                'avatar' => $owner?->profile_picture_url,
            ];
            $data['agent_unread'] = (int) $conv->agent_unread;
            $data['assigned_agent_id'] = $conv->assigned_agent_id;
            $data['context'] = $conv->meta['bot_context'] ?? [];
        } else {
            $data['user_unread'] = (int) $conv->user_unread;
        }

        return $data;
    }
}
