<?php

namespace App\Services\Bot;

use App\Models\Exam;
use App\Models\File;
use App\Models\FormSubmission;
use App\Models\EmailCampaign;
use App\Models\Notification;
use App\Models\SystemSubscription;
use App\Models\User;

/**
 * Answers "about me / about the system right now" questions directly from the
 * database — instantly and with ZERO API cost. Every lookup is scoped to the
 * signed-in user's own data (or institution-wide facts that everyone may see),
 * so the bot can never surface something the user couldn't already view.
 *
 * Runs BEFORE the knowledge base, but each intent is gated on quantitative /
 * status phrasing ("how many", "awaiting", "do I have", "status") so it never
 * hijacks a genuine how-to question.
 */
class LiveDataResolver
{
    /**
     * @return array{answer:string,links:array,matched_key:string}|null
     */
    public function resolve(string $question, User $user): ?array
    {
        $q = strtolower($question);

        // Ordered most-specific first.
        return $this->formsAwaiting($q, $user)
            ?? $this->formsMine($q, $user)
            ?? $this->unreadMemos($q, $user)
            ?? $this->notifications($q, $user)
            ?? $this->subscription($q)
            ?? $this->myArchive($q, $user)
            ?? $this->dateTime($q);
    }

    private function has(string $q, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($q, $n)) {
                return true;
            }
        }
        return false;
    }

    private function link(string $route): string
    {
        try {
            return route($route, [], false);
        } catch (\Throwable $e) {
            return '/';
        }
    }

    private function statusPhrasing(string $q): bool
    {
        return $this->has($q, [
            'how many', 'do i have', 'have i got', 'any ', 'is there', 'are there',
            'awaiting', 'waiting', 'pending', 'assigned', 'status', 'count', 'number of',
            'what is my', "what's my", 'show me my', 'unread', 'outstanding',
        ]);
    }

    // ── Forms awaiting the user's signature ──────────────────────────────────
    private function formsAwaiting(string $q, User $user): ?array
    {
        if (!$this->has($q, ['form', 'requisition', 'approval', 'sign', 'pwa', ' pr '])) {
            return null;
        }
        if (!$this->has($q, ['await', 'waiting', 'pending', 'assigned', 'to sign', 'my action', 'need', 'approve', 'my desk', 'outstanding'])) {
            return null;
        }

        try {
            $count = FormSubmission::query()->awaitingUser($user->id)->count();
        } catch (\Throwable $e) {
            return null;
        }

        $answer = $count === 0
            ? "You have **no forms awaiting your action** right now. 🎉"
            : "You have **{$count} form" . ($count === 1 ? '' : 's') . " awaiting your action**. Open the Forms Portal to sign or review " . ($count === 1 ? 'it' : 'them') . ".";

        return [
            'answer'      => $answer,
            'links'       => [['label' => 'Forms Portal', 'url' => $this->link('admin.forms.portal')]],
            'matched_key' => 'live:forms_awaiting',
        ];
    }

    // ── Forms the user created ───────────────────────────────────────────────
    private function formsMine(string $q, User $user): ?array
    {
        if (!$this->has($q, ['form', 'requisition'])) {
            return null;
        }
        if (!$this->has($q, ['my form', 'i created', 'i raised', 'i submitted', 'raised by me', 'created by me', 'my requisition'])) {
            return null;
        }

        try {
            $count = FormSubmission::query()->createdBy($user->id)->count();
        } catch (\Throwable $e) {
            return null;
        }

        return [
            'answer'      => "You've raised **{$count} form" . ($count === 1 ? '' : 's') . "** in total. You can track " . ($count === 1 ? 'it' : 'them') . " from the Forms Portal.",
            'links'       => [['label' => 'Forms Portal', 'url' => $this->link('admin.forms.portal')]],
            'matched_key' => 'live:forms_mine',
        ];
    }

    // ── Unread memos ─────────────────────────────────────────────────────────
    private function unreadMemos(string $q, User $user): ?array
    {
        if (!$this->has($q, ['memo', 'uimms', 'message'])) {
            return null;
        }
        if (!$this->statusPhrasing($q) && !$this->has($q, ['unread', 'new memo'])) {
            return null;
        }

        try {
            $count = EmailCampaign::countUnreadPendingForUser($user->id);
        } catch (\Throwable $e) {
            return null;
        }

        $answer = $count === 0
            ? "You're all caught up — **no unread memos**. ✅"
            : "You have **{$count} unread memo" . ($count === 1 ? '' : 's') . "** in the portal.";

        return [
            'answer'      => $answer,
            'links'       => [['label' => 'Open Memos', 'url' => $this->link('dashboard.uimms.portal')]],
            'matched_key' => 'live:unread_memos',
        ];
    }

    // ── Unread notifications ─────────────────────────────────────────────────
    private function notifications(string $q, User $user): ?array
    {
        if (!$this->has($q, ['notification', 'alert'])) {
            return null;
        }
        if (!$this->statusPhrasing($q) && !$this->has($q, ['unread', 'new'])) {
            return null;
        }

        try {
            $count = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        } catch (\Throwable $e) {
            return null;
        }

        $answer = $count === 0
            ? "No unread notifications right now. ✅"
            : "You have **{$count} unread notification" . ($count === 1 ? '' : 's') . "**.";

        return [
            'answer'      => $answer,
            'links'       => [['label' => 'Notifications', 'url' => $this->link('dashboard.notifications')]],
            'matched_key' => 'live:notifications',
        ];
    }

    // ── Institution subscription status (visible to all staff) ───────────────
    private function subscription(string $q): ?array
    {
        if (!$this->has($q, ['subscription', 'licence', 'license', 'expire', 'expiry', 'renew', 'billing', 'plan valid', 'when does'])) {
            return null;
        }
        if (!$this->statusPhrasing($q) && !$this->has($q, ['when', 'expire', 'expiry', 'valid', 'renew', 'status', 'active'])) {
            return null;
        }

        try {
            $sub = SystemSubscription::query()->latest('subscription_end_date')->first();
        } catch (\Throwable $e) {
            return null;
        }
        if (!$sub) {
            return null;
        }

        $status = ucfirst(str_replace('_', ' ', (string) $sub->status));
        $end    = optional($sub->subscription_end_date)->format('j M Y');
        $days   = null;
        try {
            $days = $sub->days_until_expiry;
        } catch (\Throwable $e) {
            // attribute may be unavailable in some states
        }

        $answer = "The institution's subscription is currently **{$status}**";
        if ($end) {
            $answer .= ", valid until **{$end}**";
        }
        if (is_int($days)) {
            $answer .= $days >= 0 ? " (~{$days} day" . ($days === 1 ? '' : 's') . " left)" : " (expired)";
        }
        $answer .= ". Invoices and receipts are under Payment History.";

        return [
            'answer'      => $answer,
            'links'       => [['label' => 'Payment History', 'url' => $this->link('dashboard.payment-history.index')]],
            'matched_key' => 'live:subscription',
        ];
    }

    // ── My archive counts ────────────────────────────────────────────────────
    private function myArchive(string $q, User $user): ?array
    {
        $wantsFiles = $this->has($q, ['file', 'document']);
        $wantsExams = $this->has($q, ['exam', 'paper', 'past question', 'question']);
        if (!$wantsFiles && !$wantsExams) {
            return null;
        }
        if (!$this->statusPhrasing($q) && !$this->has($q, ['my '])) {
            return null;
        }

        try {
            if ($wantsExams && !$wantsFiles) {
                $count = Exam::where('user_id', $user->id)->count();
                return [
                    'answer'      => "You have **{$count} exam" . ($count === 1 ? '' : 's') . "** in your archive.",
                    'links'       => [['label' => 'My Exams', 'url' => $this->link('dashboard.all.exams')]],
                    'matched_key' => 'live:my_exams',
                ];
            }
            if ($wantsFiles && !$wantsExams) {
                $count = File::where('user_id', $user->id)->count();
                return [
                    'answer'      => "You have **{$count} file" . ($count === 1 ? '' : 's') . "** in your archive.",
                    'links'       => [['label' => 'My Files', 'url' => $this->link('dashboard.all.files')]],
                    'matched_key' => 'live:my_files',
                ];
            }
            // both mentioned
            $files = File::where('user_id', $user->id)->count();
            $exams = Exam::where('user_id', $user->id)->count();
            return [
                'answer'      => "You have **{$exams} exam" . ($exams === 1 ? '' : 's') . "** and **{$files} file" . ($files === 1 ? '' : 's') . "** in your archive.",
                'links'       => [
                    ['label' => 'My Exams', 'url' => $this->link('dashboard.all.exams')],
                    ['label' => 'My Files', 'url' => $this->link('dashboard.all.files')],
                ],
                'matched_key' => 'live:my_archive',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ── Date / time (fun, zero cost) ─────────────────────────────────────────
    private function dateTime(string $q): ?array
    {
        if (!$this->has($q, ["today's date", 'what date', 'what day', 'current date', 'what time', 'the time', 'date today', 'day is it'])) {
            return null;
        }

        return [
            'answer'      => 'Today is **' . now()->format('l, j F Y') . '**.',
            'links'       => [],
            'matched_key' => 'live:datetime',
        ];
    }
}
