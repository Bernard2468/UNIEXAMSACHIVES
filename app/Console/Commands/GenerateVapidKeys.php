<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate {--force : Print keys even if env already has them}';

    protected $description = 'Generate VAPID public/private keys for Web Push. Run ONCE per environment.';

    public function handle(): int
    {
        if (!class_exists(VAPID::class)) {
            $this->error('minishlink/web-push is not installed.');
            $this->line('Run: composer require minishlink/web-push');
            return self::FAILURE;
        }

        $existingPub  = (string) config('services.webpush.vapid_public_key');
        $existingPriv = (string) config('services.webpush.vapid_private_key');

        if (!$this->option('force') && $existingPub !== '' && $existingPriv !== '') {
            $this->warn('VAPID keys are already set in your .env. Re-running will invalidate every existing push subscription.');
            if (!$this->confirm('Continue and generate NEW keys anyway?', false)) {
                return self::SUCCESS;
            }
        }

        $keys = VAPID::createVapidKeys();

        $this->line('');
        $this->info('VAPID keys generated. Add the following to your .env (no quotes):');
        $this->line('');
        $this->line('  VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('  VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('  VAPID_SUBJECT="mailto:cug@academicdigital.space"   # change to your email');
        $this->line('');
        $this->warn('Keep VAPID_PRIVATE_KEY secret — do not commit it.');
        $this->line('After saving, run: php artisan config:cache  (production only)');

        return self::SUCCESS;
    }
}
