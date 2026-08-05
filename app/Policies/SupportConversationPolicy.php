<?php

namespace App\Policies;

use App\Models\BotConversation;
use App\Models\User;

/**
 * Single source of truth for support-chat authorization.
 *
 * Ownership model (industry-standard, conflict-free):
 *   - VIEW  — the requester who owns the thread, OR any support agent (shared
 *             read-only inbox, so every agent keeps a record).
 *   - REPLY — the requester (always, on their own thread), OR the assigned
 *             agent. An UNassigned thread may be replied to by any agent, which
 *             atomically claims it. Once claimed, no other agent can reply — they
 *             see it read-only. A Super Admin must TAKE OVER (claim) first, so
 *             two people are never talking to the same user at once.
 *   - CLAIM — any agent on an unassigned thread; a Super Admin may also take over
 *             an assigned one.
 *   - DELETE — only the requester (soft-hide from their own history).
 *
 * Super Admins bypass every ability EXCEPT `reply` (see before()).
 */
class SupportConversationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            // Chatting to a user in a thread owned by another agent still requires
            // taking it over first — so we never get two agents replying at once.
            return $ability === 'reply' ? null : true;
        }
        return null;
    }

    private function owns(User $user, BotConversation $conversation): bool
    {
        return (int) $conversation->user_id === (int) $user->id;
    }

    private function assignedToMe(User $user, BotConversation $conversation): bool
    {
        return $conversation->assigned_agent_id
            && (int) $conversation->assigned_agent_id === (int) $user->id;
    }

    /** View / poll a thread (records for every agent). */
    public function view(User $user, BotConversation $conversation): bool
    {
        return $conversation->isSupport()
            && ($this->owns($user, $conversation) || $user->isSupportAgent());
    }

    /**
     * Post a message.
     *  - Owner (requester): only while the chat is still open. Once an agent has
     *    RESOLVED it, the user cannot reply — they must start a new chat (top
     *    assistants like X/Google close a resolved thread this way).
     *  - Agent: only if the thread is unassigned (→claims) or already theirs.
     */
    public function reply(User $user, BotConversation $conversation): bool
    {
        if (!$conversation->isSupport()) {
            return false;
        }
        if ($this->owns($user, $conversation)) {
            return !$conversation->isResolved();
        }
        if (!$user->isSupportAgent()) {
            return false;
        }
        return !$conversation->assigned_agent_id || $this->assignedToMe($user, $conversation);
    }

    /** Claim an unassigned thread (any agent); Super Admin may take over an assigned one. */
    public function claim(User $user, BotConversation $conversation): bool
    {
        if (!$conversation->isSupport() || !$user->isSupportAgent()) {
            return false;
        }
        return !$conversation->assigned_agent_id; // Super Admin passes via before()
    }

    /** Resolve — owner, the assigned agent, or an agent on an unclaimed thread. */
    public function resolve(User $user, BotConversation $conversation): bool
    {
        if (!$conversation->isSupport()) {
            return false;
        }
        if ($this->owns($user, $conversation)) {
            return true;
        }
        if (!$user->isSupportAgent()) {
            return false;
        }
        return !$conversation->assigned_agent_id || $this->assignedToMe($user, $conversation);
    }

    /** Agent actions like reopen — only the assignee (or an unclaimed thread). */
    public function manage(User $user, BotConversation $conversation): bool
    {
        if (!$conversation->isSupport() || !$user->isSupportAgent()) {
            return false;
        }
        return !$conversation->assigned_agent_id || $this->assignedToMe($user, $conversation);
    }

    /** Clear a chat from history — only the requester who owns it. */
    public function delete(User $user, BotConversation $conversation): bool
    {
        return $conversation->isSupport() && $this->owns($user, $conversation);
    }
}
