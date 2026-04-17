<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionManager;

class CheckExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:check-expiring';

    /**
     * The console command description.
     */
    protected $description = 'Check and update status of expiring subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking expiring subscriptions...');

        $subscriptionManager = new SubscriptionManager();
        $results = $subscriptionManager->checkExpiringSubscriptions();

        $this->info("Checked: {$results['checked']} subscriptions");
        $this->info("Expiring soon: {$results['expiring_soon']}");
        $this->info("Expired: {$results['expired']}");
        $this->info("Suspended: {$results['suspended']}");

        $this->info('Subscription check completed successfully.');

        return Command::SUCCESS;
    }
}

