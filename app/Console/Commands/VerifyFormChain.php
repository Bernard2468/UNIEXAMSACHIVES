<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use Illuminate\Console\Command;

/**
 * Forensic verification of a form submission's signature hash chain.
 *
 * Usage:
 *   php artisan forms:verify FRM-CUG1234
 *   php artisan forms:verify 17               # also accepts numeric id
 *   php artisan forms:verify --all            # walk every submission, report failures
 *
 * Exits non-zero if any chain is invalid, so it's safe to wire into CI.
 */
class VerifyFormChain extends Command
{
    protected $signature = 'forms:verify
                            {reference? : Submission reference (FRM-CUG####) or numeric id}
                            {--all : Verify every submission in the database}';

    protected $description = 'Re-derive each form signature\'s chain hash and report tampering.';

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->verifyAll();
        }

        $ref = $this->argument('reference');
        if (!$ref) {
            $this->error('Pass a submission reference or numeric id, or use --all.');
            return self::INVALID;
        }

        $submission = is_numeric($ref)
            ? FormSubmission::find((int) $ref)
            : FormSubmission::where('reference', $ref)->first();

        if (!$submission) {
            $this->error("Submission [{$ref}] not found.");
            return self::FAILURE;
        }

        return $this->verifyOne($submission) ? self::SUCCESS : self::FAILURE;
    }

    protected function verifyAll(): int
    {
        $bad = 0;
        FormSubmission::with('signatures')->chunk(100, function ($chunk) use (&$bad) {
            foreach ($chunk as $submission) {
                if (!$this->verifyOne($submission, quiet: true)) {
                    $bad++;
                }
            }
        });

        if ($bad === 0) {
            $this->info('All form submissions verified clean.');
            return self::SUCCESS;
        }

        $this->error("{$bad} submission(s) failed chain verification. Re-run without --all on each reference for details.");
        return self::FAILURE;
    }

    protected function verifyOne(FormSubmission $submission, bool $quiet = false): bool
    {
        $signatures = $submission->signatures()->orderBy('signed_at')->orderBy('id')->get();

        if ($signatures->isEmpty()) {
            if (!$quiet) {
                $this->warn("[{$submission->reference}] has no signatures yet.");
            }
            return true;
        }

        $rows = [];
        $allValid = true;

        foreach ($signatures as $sig) {
            $check = $sig->verifyChain();
            if (!$check['valid']) {
                $allValid = false;
            }
            $rows[] = [
                $sig->stage_slug,
                trim((optional($sig->user)->first_name ?? '') . ' ' . (optional($sig->user)->last_name ?? '')),
                optional($sig->signed_at)->format('Y-m-d H:i'),
                substr($sig->chain_hash, 0, 12) . '…',
                $check['valid'] ? '✓ VERIFIED' : '✗ ' . strtoupper($check['reason'] ?? 'mismatch'),
            ];
        }

        if ($quiet && $allValid) {
            return true;
        }

        if ($quiet) {
            $this->error("[{$submission->reference}] chain INVALID");
            return false;
        }

        $this->line('');
        $this->line("Submission <fg=cyan>{$submission->reference}</> · {$submission->form_code} · status={$submission->status}");
        $this->table(['Stage', 'Signer', 'Signed at', 'Chain hash', 'Verdict'], $rows);

        if ($allValid) {
            $this->info('Chain intact across all stages.');
        } else {
            $this->error('Chain verification FAILED. Section data, signer identity, or signature order has been altered after signing.');
        }

        return $allValid;
    }
}
