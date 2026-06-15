<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\Push\WebPushService;
use Illuminate\Console\Command;

/**
 * Operational visibility for Web Push. Read-only by default; with --test it
 * dispatches a real push to every device of a given user so you can confirm
 * end-to-end delivery from the production server.
 *
 *   php artisan push:diagnose
 *   php artisan push:diagnose --test=42
 */
class PushDiagnose extends Command
{
    protected $signature = 'push:diagnose
        {--test= : Send a real test push to every device of this user id}
        {--prune : Delete orphaned subscriptions whose user no longer exists}';

    protected $description = 'Report Web Push config + per-user subscription health, optionally send a test push.';

    public function handle(WebPushService $push): int
    {
        $pub  = (string) config('services.webpush.vapid_public_key');
        $priv = (string) config('services.webpush.vapid_private_key');

        $libInstalled = class_exists(\Minishlink\WebPush\WebPush::class);

        $this->info('Web Push configuration');
        $this->line('  Library (minishlink/web-push): ' . ($libInstalled ? 'INSTALLED' : 'MISSING — no pushes can be sent'));
        $this->line('  VAPID public key : ' . ($pub !== '' ? substr($pub, 0, 12) . '… (' . strlen($pub) . ' chars)' : 'MISSING'));
        $this->line('  VAPID private key: ' . ($priv !== '' ? 'set (' . strlen($priv) . ' chars)' : 'MISSING'));
        $this->line('  Subject          : ' . config('services.webpush.vapid_subject'));

        if (!$libInstalled) {
            $this->error('minishlink/web-push is NOT loadable. Run `composer require minishlink/web-push` and COMMIT composer.json + composer.lock so deploys stop removing it.');
        }
        if ($pub === '' || $priv === '') {
            $this->error('VAPID keys are not fully configured — no pushes will be sent. Set them in .env and run `php artisan config:cache`.');
        }
        $this->newLine();

        if ($this->option('prune')) {
            $deleted = PushSubscription::whereNotIn('user_id', User::query()->select('id'))->delete();
            $this->warn("Pruned {$deleted} orphaned subscription(s) (user no longer exists).");
            $this->newLine();
        }

        $total = PushSubscription::count();
        $this->info("Subscriptions on file: {$total}");

        $rows = PushSubscription::selectRaw('user_id, count(*) as devices, max(last_used_at) as last_used')
            ->groupBy('user_id')
            ->get();

        if ($rows->isNotEmpty()) {
            $this->table(
                ['User ID', 'User', 'push_enabled', 'Devices', 'Last used'],
                $rows->map(function ($r) {
                    $u = User::find($r->user_id);
                    // The users table uses first_name/last_name (no `name` column),
                    // so build a label from those, falling back to email.
                    $name = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : '';
                    $label = $u
                        ? ($name !== '' ? $name : ($u->email ?? ('user #' . $r->user_id)))
                        : '✗ MISSING (no user row)';

                    return [
                        $r->user_id,
                        $label,
                        $u ? (($u->push_enabled ?? true) ? 'yes' : 'NO (toggled off)') : '-',
                        $r->devices,
                        $r->last_used ?: 'never',
                    ];
                })->all()
            );
        }

        $testUserId = $this->option('test');
        if ($testUserId !== null && $testUserId !== '') {
            $subs = PushSubscription::where('user_id', $testUserId)->get();
            if ($subs->isEmpty()) {
                $this->warn("No subscriptions on file for user {$testUserId}.");
                return self::SUCCESS;
            }
            foreach ($subs as $sub) {
                $res    = $push->sendTest($sub, 'Test push', 'If you can see this, push delivery is working.');
                $status = $res['status'] ?? '—';
                if ($res['ok']) {
                    $this->info("  → subscription #{$sub->id}: ACCEPTED by push service (HTTP {$status}). It should appear on the device.");
                } else {
                    $this->error("  → subscription #{$sub->id}: REJECTED (HTTP {$status}) — {$res['reason']}");
                }
            }
            $this->newLine();
            $this->line('Interpreting the result:');
            $this->line('  • ACCEPTED (201) but nothing shows  → browser/OS side: browser fully closed, OS notifications off for the browser, or Focus Assist / Do Not Disturb on.');
            $this->line('  • REJECTED 403                       → VAPID key mismatch (server key ≠ the key the device subscribed with). User must re-subscribe.');
            $this->line('  • REJECTED 404/410                   → dead endpoint (now auto-pruned). User must re-subscribe.');
        }

        return self::SUCCESS;
    }
}
