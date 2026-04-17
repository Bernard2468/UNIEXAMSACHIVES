<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendCampaignEmail;
use App\Models\EmailCampaign;

class ProcessEmailQueue extends Command
{
    protected $signature = 'queue:process-emails {--check-status}';
    protected $description = 'Process pending email queue jobs and check status';

    public function handle()
    {
        if ($this->option('check-status')) {
            $this->checkQueueStatus();
            return;
        }

        $this->info("🚀 Processing email queue...");
        
        // Check for pending jobs
        $pendingJobs = DB::table('jobs')->where('queue', 'default')->count();
        $this->info("📧 Pending jobs in queue: {$pendingJobs}");
        
        if ($pendingJobs > 0) {
            $this->info("⚙️ Processing jobs...");
            
            // Process the queue
            $this->call('queue:work', [
                '--stop-when-empty' => true,
                '--timeout' => 300,
            ]);
        }
        
        // Check campaign status
        $this->checkCampaignStatus();
        
        $this->info("✅ Queue processing completed!");
    }
    
    private function checkQueueStatus()
    {
        $this->info("📊 Queue Status Report");
        $this->line("═══════════════════════");
        
        // Check pending jobs
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        $this->info("📬 Pending jobs: {$pendingJobs}");
        $this->info("❌ Failed jobs: {$failedJobs}");
        
        // Check campaign status
        $this->checkCampaignStatus();
        
        // Check recent logs
        $this->info("\n📋 Recent Email Logs:");
        $this->line("─────────────────────");
        
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                $emailLogs = array_slice(
                    array_filter(
                        explode("\n", $logs), 
                        fn($line) => str_contains($line, 'Resend') || str_contains($line, 'email')
                    ), 
                    -10
                );
                
                foreach ($emailLogs as $log) {
                    $this->line($log);
                }
            } else {
                $this->warn("No log file found at: {$logFile}");
            }
        } catch (Exception $e) {
            $this->error("Failed to read logs: " . $e->getMessage());
        }
    }
    
    private function checkCampaignStatus()
    {
        $this->info("\n📈 Campaign Status:");
        $this->line("─────────────────────");
        
        $campaigns = EmailCampaign::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        foreach ($campaigns as $campaign) {
            $this->info("  {$campaign->status}: {$campaign->count} campaigns");
        }
        
        // Check for stuck campaigns
        $stuckCampaigns = EmailCampaign::where('status', 'sending')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->count();
            
        if ($stuckCampaigns > 0) {
            $this->warn("⚠️  {$stuckCampaigns} campaigns appear to be stuck in 'sending' status");
        }
    }
}