<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Console\Command;

class CheckCampaignErrors extends Command
{
    protected $signature = 'check:campaign-errors {--latest} {--campaign-id=}';
    protected $description = 'Check error messages from failed campaign emails';

    public function handle()
    {
        $this->info("🔍 Checking Campaign Email Errors...");

        if ($this->option('campaign-id')) {
            $campaign = EmailCampaign::find($this->option('campaign-id'));
            if (!$campaign) {
                $this->error("Campaign not found!");
                return 1;
            }
            $campaigns = collect([$campaign]);
        } else {
            // Get latest campaigns (last 10)
            $campaigns = EmailCampaign::orderBy('created_at', 'desc')->limit(10)->get();
        }

        foreach ($campaigns as $campaign) {
            $this->line("\n═══════════════════════════════════");
            $this->info("📋 Campaign ID: {$campaign->id}");
            $this->line("📝 Subject: {$campaign->subject}");
            $this->line("📅 Created: {$campaign->created_at}");
            $this->line("📊 Status: {$campaign->status}");
            $this->line("✅ Sent: {$campaign->sent_count}");
            $this->line("❌ Failed: {$campaign->failed_count}");
            $this->line("👥 Total Recipients: {$campaign->total_recipients}");

            // Get failed recipients
            $failedRecipients = $campaign->recipients()->where('status', 'failed')->get();
            
            if ($failedRecipients->count() > 0) {
                $this->warn("\n❌ FAILED RECIPIENTS:");
                foreach ($failedRecipients as $recipient) {
                    $this->line("  📧 {$recipient->user->email}");
                    if ($recipient->error_message) {
                        $this->error("     Error: {$recipient->error_message}");
                    } else {
                        $this->warn("     No error message recorded");
                    }
                }
            }

            // Get sent recipients  
            $sentRecipients = $campaign->recipients()->where('status', 'sent')->get();
            if ($sentRecipients->count() > 0) {
                $this->info("\n✅ SENT RECIPIENTS:");
                foreach ($sentRecipients as $recipient) {
                    $this->line("  📧 {$recipient->user->email} (sent at: {$recipient->sent_at})");
                }
            }

            // Get pending recipients
            $pendingRecipients = $campaign->recipients()->where('status', 'pending')->get();
            if ($pendingRecipients->count() > 0) {
                $this->warn("\n⏳ PENDING RECIPIENTS:");
                foreach ($pendingRecipients as $recipient) {
                    $this->line("  📧 {$recipient->user->email}");
                }
            }

            // Show attachments info
            if ($campaign->attachments && count($campaign->attachments) > 0) {
                $this->info("\n📎 ATTACHMENTS:");
                foreach ($campaign->attachments as $attachment) {
                    $filePath = storage_path('app/public/' . $attachment['path']);
                    $exists = file_exists($filePath);
                    $this->line("  📄 {$attachment['name']} - " . ($exists ? "✅ EXISTS" : "❌ MISSING"));
                    if (!$exists) {
                        $this->error("     Missing file: {$filePath}");
                    }
                }
            }

            if ($this->option('latest')) {
                break; // Only show latest if --latest flag is used
            }
        }

        return 0;
    }
}