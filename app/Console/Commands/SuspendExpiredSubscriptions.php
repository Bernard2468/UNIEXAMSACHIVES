<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionManager;

class SuspendExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:suspend-expired';

    /**
     * The console command description.
     */
    protected $description = 'Suspend subscriptions that have expired beyond grace period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions beyond grace period...');

        $subscriptionManager = new SubscriptionManager();
        $results = $subscriptionManager->suspendExpiredSubscriptions();

        $this->info("Checked: {$results['checked']} expired subscriptions");
        $this->info("Suspended: {$results['suspended']} subscriptions");

        if ($results['suspended'] > 0) {
            $this->warn("{$results['suspended']} subscription(s) have been suspended due to non-payment.");
        }

        $this->info('Suspension check completed.');

        return Command::SUCCESS;
    }
}

