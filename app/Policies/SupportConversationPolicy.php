<?php

namespace App\Policies;

use App\Models\BotConversation;
use App\Models\User;

/**
 * Single source of truth for support-chat authorization.
 *
 * Visibility: the requester who owns the thread, OR any support agent (shared
 * inbox model — Support office members + Institutional Admins + Super Admins).
 * Super Admins auto-pass via the before() hook, matching the Forms policy.
 */
class SupportConversationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    private function owns(User $user, BotConversation $conversation): bool
    {
        return (int) $conversation->user_id === (int) $user->id;
    }

    /** View / poll a thread. */
    public function view(User $user, BotConversation $conversation): bool
    {
        return $conversation->isSupport()
            && ($this->owns($user, $conversation) || $user->isSupportAgent());
    }

    /** Post a message (owner reply or agent reply). */
    public function reply(User $user, BotConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /** Resolve / reopen — either side may close; agents may reopen. */
    public function resolve(User $user, BotConversation $conversation): bool
    {
        return $conversation->isSupport()
            && ($this->owns($user, $conversation) || $user->isSupportAgent());
    }

    /** Agent-only actions: claim, internal notes, reopen. */
    public function manage(User $user, BotConversation $conversation): bool
    {
        return $conversation->isSupport() && $user->isSupportAgent();
    }
}
