<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionManager;

class ProcessAutoRenewals extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:process-auto-renewals';

    /**
     * The console command description.
     */
    protected $description = 'Process automatic renewals for subscriptions with auto-renewal enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing auto-renewals...');

        $subscriptionManager = new SubscriptionManager();
        $results = $subscriptionManager->processAutoRenewals();

        $this->info("Checked: {$results['checked']} subscriptions");
        $this->info("Attempted: {$results['attempted']} renewals");
        $this->info("Successful: {$results['successful']}");
        $this->info("Failed: {$results['failed']}");

        $this->info('Auto-renewal processing completed.');

        return Command::SUCCESS;
    }
}

