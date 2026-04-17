<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignEmail;
use App\Models\EmailCampaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStuckCampaigns extends Command
{
    protected $signature = 'campaigns:process-stuck {--reset} {--retry-failed}';
    protected $description = 'Process campaigns stuck in sending status and optionally reset or retry failed ones';

    public function handle()
    {
        $this->info("🔍 Checking for stuck campaigns...");
        
        // Find campaigns stuck in "sending" status for more than 10 minutes
        $stuckCampaigns = EmailCampaign::where('status', 'sending')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->get();

        if ($stuckCampaigns->count() > 0) {
            $this->warn("⚠️  Found {$stuckCampaigns->count()} stuck campaigns");
            
            foreach ($stuckCampaigns as $campaign) {
                $this->line("Campaign ID: {$campaign->id} - Subject: {$campaign->subject}");
                
                if ($this->option('reset')) {
                    $this->info("🔄 Resetting campaign {$campaign->id} to draft status...");
                    
                    // Reset campaign status
                    $campaign->update(['status' => 'draft']);
                    
                    // Reset all recipients to pending
                    $campaign->recipients()->update(['status' => 'pending', 'sent_at' => null, 'error_message' => null]);
                    
                    $this->info("✅ Campaign {$campaign->id} has been reset");
                } else {
                    // Just retry the stuck campaign
                    $this->info("🚀 Retrying campaign {$campaign->id}...");
                    
                    // Reset to draft first, then dispatch
                    $campaign->update(['status' => 'draft']);
                    $campaign->recipients()->where('status', 'failed')->update(['status' => 'pending', 'error_message' => null]);
                    
                    // Dispatch the job again
                    SendCampaignEmail::dispatch($campaign);
                    
                    $this->info("✅ Campaign {$campaign->id} has been re-queued");
                }
            }
        } else {
            $this->info("✅ No stuck campaigns found");
        }
        
        // Handle failed campaigns if requested
        if ($this->option('retry-failed')) {
            $this->info("\n🔁 Checking for failed campaigns to retry...");
            
            $failedCampaigns = EmailCampaign::where('status', 'failed')->get();
            
            if ($failedCampaigns->count() > 0) {
                $this->warn("Found {$failedCampaigns->count()} failed campaigns");
                
                foreach ($failedCampaigns as $campaign) {
                    $this->line("Retrying Campaign ID: {$campaign->id} - Subject: {$campaign->subject}");
                    
                    // Reset to draft and retry
                    $campaign->update(['status' => 'draft']);
                    $campaign->recipients()->update(['status' => 'pending', 'sent_at' => null, 'error_message' => null]);
                    
                    // Dispatch the job again
                    SendCampaignEmail::dispatch($campaign);
                    
                    $this->info("✅ Campaign {$campaign->id} has been re-queued");
                }
            } else {
                $this->info("✅ No failed campaigns found");
            }
        }
        
        // Show current status summary
        $this->showStatusSummary();
        
        // Check queue status
        $this->checkQueueStatus();
        
        return 0;
    }
    
    private function showStatusSummary()
    {
        $this->info("\n📊 Campaign Status Summary:");
        $this->line("═══════════════════════════");
        
        $statusCounts = EmailCampaign::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        foreach (['draft', 'scheduled', 'sending', 'sent', 'failed'] as $status) {
            $count = $statusCounts[$status] ?? 0;
            $icon = $this->getStatusIcon($status);
            $this->line("{$icon} {$status}: {$count}");
        }
    }
    
    private function getStatusIcon($status)
    {
        return match($status) {
            'draft' => '📝',
            'scheduled' => '⏰',
            'sending' => '📤',
            'sent' => '✅',
            'failed' => '❌',
            default => '📋'
        };
    }
    
    private function checkQueueStatus()
    {
        $this->info("\n🚦 Queue Status:");
        $this->line("═══════════════");
        
        $pendingJobs = DB::table('jobs')->where('queue', 'default')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        $this->line("📬 Pending jobs: {$pendingJobs}");
        $this->line("❌ Failed jobs: {$failedJobs}");
        
        if ($pendingJobs > 0) {
            $this->warn("\n💡 To process pending jobs, run: php artisan queue:work");
        }
        
        if ($failedJobs > 0) {
            $this->warn("💡 To retry failed jobs, run: php artisan queue:retry all");
        }
    }
}