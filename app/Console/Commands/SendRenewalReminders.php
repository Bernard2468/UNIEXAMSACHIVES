<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionManager;

class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send renewal reminder notifications to subscription owners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending renewal reminders...');

        $subscriptionManager = new SubscriptionManager();
        $results = $subscriptionManager->sendRenewalReminders();

        $this->info("Checked: {$results['checked']} subscriptions");
        $this->info("Reminders sent: {$results['reminders_sent']}");

        $this->info('Renewal reminders sent successfully.');

        return Command::SUCCESS;
    }
}

