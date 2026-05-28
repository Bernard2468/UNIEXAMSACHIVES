<?php

namespace App\Console\Commands;

use App\Models\FormSignature;
use App\Models\FormSubmission;
use App\Services\Signing\SignatureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time repair for signatures whose chain_hash was generated against a
 * microsecond-bearing ISO timestamp before InAppSignatureProvider was pinned
 * to whole-second precision. The DB column stores at precision 0, so every
 * affected row fails verifyChain() with reason=chain_mismatch.
 *
 *   php artisan forms:repair-chains              # dry run, reports what would change
 *   php artisan forms:repair-chains --apply      # actually rewrite chain hashes
 *
 * The command will REFUSE to touch a row whose payload_hash no longer matches
 * its current section_data — that indicates real tampering, and we don't want
 * to mask it.
 */
class RepairFormChains extends Command
{
    protected $signature = 'forms:repair-chains
                            {--apply : Persist the recomputed hashes (default: dry run)}';

    protected $description = 'Recompute signature chain hashes for legacy rows broken by the signed_at precision bug.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (!$apply) {
            $this->warn('DRY RUN — no rows will be written. Use --apply to commit.');
        }

        $repaired = 0;
        $skippedTampered = 0;
        $skippedClean = 0;

        FormSubmission::with(['signatures' => function ($q) {
            $q->orderBy('signed_at')->orderBy('id');
        }])->chunk(50, function ($submissions) use (&$repaired, &$skippedTampered, &$skippedClean, $apply) {
            foreach ($submissions as $submission) {
                $priorHash = null;

                foreach ($submission->signatures as $sig) {
                    $payload = $submission->sectionData($sig->stage_slug);
                    $expectedPayload = SignatureService::payloadHash($payload);

                    if ($expectedPayload !== $sig->payload_hash) {
                        $this->warn(sprintf(
                            '[%s] stage=%s — payload_hash differs from current section_data. Refusing to repair (possible tampering).',
                            $submission->reference,
                            $sig->stage_slug
                        ));
                        $skippedTampered++;
                        $priorHash = $sig->chain_hash;
                        continue;
                    }

                    $signedAtIso = optional($sig->signed_at)->toISOString() ?? '';
                    $expectedChain = SignatureService::chainHash(
                        $priorHash,
                        $expectedPayload,
                        (int) $sig->user_id,
                        $signedAtIso
                    );

                    $needsPriorRewrite = $sig->prior_hash !== $priorHash;
                    $needsChainRewrite = $sig->chain_hash !== $expectedChain;

                    if (!$needsPriorRewrite && !$needsChainRewrite) {
                        $skippedClean++;
                        $priorHash = $sig->chain_hash;
                        continue;
                    }

                    $this->line(sprintf(
                        '[%s] stage=%s id=%d  chain %s → %s',
                        $submission->reference,
                        $sig->stage_slug,
                        $sig->id,
                        substr($sig->chain_hash, 0, 10) . '…',
                        substr($expectedChain, 0, 10) . '…'
                    ));

                    if ($apply) {
                        DB::table('form_signatures')
                            ->where('id', $sig->id)
                            ->update([
                                'prior_hash' => $priorHash,
                                'chain_hash' => $expectedChain,
                            ]);
                    }

                    $repaired++;
                    $priorHash = $expectedChain;
                }
            }
        });

        $this->line('');
        $this->info("Rows already clean:        {$skippedClean}");
        $this->info("Rows repaired:             {$repaired}" . ($apply ? '' : ' (would repair)'));
        if ($skippedTampered > 0) {
            $this->error("Rows skipped (tampered):   {$skippedTampered}");
            $this->line('Tampered rows were left alone — investigate before deciding what to do.');
        }

        return self::SUCCESS;
    }
}
