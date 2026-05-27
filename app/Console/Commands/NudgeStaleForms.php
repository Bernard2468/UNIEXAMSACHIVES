<?php

namespace App\Console\Commands;

use App\Mail\FormStaleNudge;
use App\Models\FormSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send a one-line reminder to assignees who have been sitting on an
 * in-progress form for too long.
 *
 * Rules:
 *   - Only in_progress forms with a current assignee are nudged.
 *   - First nudge fires at WARN_DAYS (default 2).
 *   - Subsequent nudges fire no more often than NUDGE_COOLDOWN_HOURS (48h)
 *     so the assignee isn't spammed every time the scheduler runs.
 *   - At ESCALATE_DAYS (default 7) the office head is added to Cc — only
 *     once per nudge, and only if they aren't the assignee themselves.
 *   - The `last_nudged_at` write goes via DB::table()->update() so it
 *     does NOT bump `updated_at` — otherwise the form would never appear
 *     stale again after being nudged.
 *
 * Manual usage:
 *   php artisan forms:nudge-stale            # run now
 *   php artisan forms:nudge-stale --dry-run  # report what would send, don't send
 */
class NudgeStaleForms extends Command
{
    protected $signature = 'forms:nudge-stale {--dry-run : Report what would be sent without sending mail}';

    protected $description = 'Email assignees and (when escalated) office heads about forms stuck in their queue.';

    public const WARN_DAYS            = 2;
    public const ESCALATE_DAYS        = 7;
    public const NUDGE_COOLDOWN_HOURS = 48;

    public function handle(): int
    {
        $now    = Carbon::now();
        $dryRun = (bool) $this->option('dry-run');

        $warnCutoff     = $now->copy()->subDays(self::WARN_DAYS);
        $cooldownCutoff = $now->copy()->subHours(self::NUDGE_COOLDOWN_HOURS);

        $candidates = FormSubmission::query()
            ->where('status', FormSubmission::STATUS_IN_PROGRESS)
            ->whereNotNull('current_assignee_id')
            ->where('updated_at', '<=', $warnCutoff)
            ->where(function ($q) use ($cooldownCutoff) {
                $q->whereNull('last_nudged_at')
                  ->orWhere('last_nudged_at', '<=', $cooldownCutoff);
            })
            ->with(['currentAssignee', 'currentOffice.users', 'creator']);

        $total      = $candidates->count();
        $sent       = 0;
        $skipped    = 0;
        $escalated  = 0;
        $failed     = 0;

        if ($total === 0) {
            $this->info('No stale forms eligible for nudging right now.');
            return self::SUCCESS;
        }

        $this->info(sprintf('%d form(s) eligible. %s', $total, $dryRun ? '(dry-run)' : ''));

        $candidates->chunkById(50, function ($chunk) use (&$sent, &$skipped, &$escalated, &$failed, $dryRun, $now) {
            foreach ($chunk as $submission) {
                $assignee = $submission->currentAssignee;
                if (!$assignee || empty($assignee->email)) {
                    $skipped++;
                    continue;
                }

                $staleDays   = (int) $submission->updated_at->diffInDays($now);
                $isEscalated = $staleDays >= self::ESCALATE_DAYS;

                // Resolve the office head for escalation Cc, if applicable.
                $ccEmails = [];
                if ($isEscalated && $submission->currentOffice) {
                    $head = $submission->currentOffice->head();
                    if ($head && $head->id !== $assignee->id && !empty($head->email)) {
                        $ccEmails[] = $head->email;
                    }
                }

                $line = sprintf(
                    '  · %s · stale %dd · to %s%s',
                    $submission->reference,
                    $staleDays,
                    $assignee->email,
                    $ccEmails ? '  cc ' . implode(',', $ccEmails) : ''
                );

                if ($dryRun) {
                    $this->line($line);
                    $sent++;
                    if ($isEscalated) $escalated++;
                    continue;
                }

                try {
                    $mail = Mail::to($assignee->email);
                    if ($ccEmails) {
                        $mail->cc($ccEmails);
                    }
                    $mail->send(new FormStaleNudge($submission, $assignee, $staleDays, $isEscalated));

                    // CRITICAL: update via the query builder so we do NOT
                    // bump updated_at — otherwise the form would stop being
                    // stale the moment we nudge it.
                    DB::table('form_submissions')
                        ->where('id', $submission->id)
                        ->update(['last_nudged_at' => $now]);

                    $this->line($line);
                    $sent++;
                    if ($isEscalated) $escalated++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('Form stale-nudge send failed', [
                        'submission_id' => $submission->id,
                        'reference'     => $submission->reference,
                        'assignee_id'   => $assignee->id,
                        'error'         => $e->getMessage(),
                    ]);
                    $this->warn("  ! failed: {$submission->reference} — {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info(sprintf(
            '%s %d, escalated %d, skipped %d, failed %d.',
            $dryRun ? 'Would have sent' : 'Sent',
            $sent,
            $escalated,
            $skipped,
            $failed
        ));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
