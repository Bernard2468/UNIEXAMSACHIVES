<?php

namespace App\Console\Commands;

use App\Mail\SupportTicketReceived;
use App\Models\BotConversation;
use App\Models\Notification;
use App\Services\Support\SupportChatService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Re-alert agents about support chats that have sat unclaimed in the queue.
 *
 * Rules:
 *   - Only queued (unassigned, unresolved) support conversations are nudged.
 *   - First nudge fires WARN_MINUTES after the chat was opened.
 *   - Subsequent nudges respect COOLDOWN_MINUTES (tracked in meta.nudged_at) so
 *     agents aren't paged every scheduler tick.
 *   - Only fires during staffed support hours — the offline ticket email
 *     already went out when the chat was created, so there's no one to page.
 *
 * Manual usage:
 *   php artisan support:nudge-stale
 *   php artisan support:nudge-stale --dry-run
 */
class NudgeStaleSupport extends Command
{
    protected $signature = 'support:nudge-stale {--dry-run : Report what would be sent without sending}';

    protected $description = 'Re-alert agents about support chats still waiting in the queue.';

    public const WARN_MINUTES     = 20;
    public const COOLDOWN_MINUTES = 60;

    public function handle(SupportChatService $support): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!$support->isEnabled()) {
            $this->info('Support chat is disabled — nothing to nudge.');
            return self::SUCCESS;
        }

        if (!$dryRun && !$support->isWithinSupportHours()) {
            $this->info('Outside support hours — no live agents to page.');
            return self::SUCCESS;
        }

        $now = Carbon::now();
        $warnCutoff = $now->copy()->subMinutes(self::WARN_MINUTES);
        $cooldown   = $now->copy()->subMinutes(self::COOLDOWN_MINUTES);

        $candidates = BotConversation::support()
            ->where('status', BotConversation::STATUS_QUEUED)
            ->whereNull('assigned_agent_id')
            ->where('created_at', '<=', $warnCutoff)
            ->get()
            ->filter(function (BotConversation $c) use ($cooldown) {
                $nudgedAt = $c->meta['nudged_at'] ?? null;
                return !$nudgedAt || Carbon::parse($nudgedAt)->lte($cooldown);
            });

        if ($candidates->isEmpty()) {
            $this->info('No queued support chats eligible for nudging.');
            return self::SUCCESS;
        }

        $recipients = $support->agentRecipients();
        $sent = 0;
        $failed = 0;

        foreach ($candidates as $conv) {
            $waited = (int) optional($conv->created_at)->diffInMinutes($now);
            $this->line("  · #{$conv->id} · waiting {$waited}m");

            if ($dryRun) {
                $sent++;
                continue;
            }

            $owner = $conv->user;
            $url = route('dashboard.support.inbox') . '?c=' . $conv->id;

            foreach ($recipients as $agent) {
                try {
                    Notification::createSupportNotification(
                        $agent->id,
                        'Support chat still waiting',
                        'A support chat has been unanswered for ' . $waited . ' minutes.',
                        $url,
                        $owner?->id,
                        ['conversation_id' => $conv->id]
                    );
                } catch (\Throwable $e) {
                }
            }

            if ($owner) {
                foreach ($recipients->take(10) as $agent) {
                    if (empty($agent->email)) {
                        continue;
                    }
                    try {
                        Mail::to($agent->email)->send(new SupportTicketReceived($conv, $owner, $agent, true));
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Support stale-nudge email failed', [
                            'conversation_id' => $conv->id,
                            'agent_id' => $agent->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $meta = $conv->meta ?? [];
            $meta['nudged_at'] = $now->toIso8601String();
            $conv->forceFill(['meta' => $meta])->save();
            $sent++;
        }

        $this->newLine();
        $this->info(sprintf('%s %d chat(s), failed %d.', $dryRun ? 'Would nudge' : 'Nudged', $sent, $failed));

        return self::SUCCESS;
    }
}
