<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailCampaign;
use Carbon\Carbon;

class AutoArchiveCompletedMemos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memos:auto-archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically archive completed memos that are older than 30 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting auto-archive process for completed memos...');
        
        try {
            // Get completed memos that are older than 30 days
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            
            $completedMemos = EmailCampaign::where('memo_status', 'completed')
                ->where('updated_at', '<=', $thirtyDaysAgo)
                ->get();
            
            $archivedCount = 0;
            
            foreach ($completedMemos as $memo) {
                // Update memo status to archived
                $memo->update(['memo_status' => 'archived']);
                
                // Add to workflow history
                $workflowHistory = $memo->workflow_history ?? [];
                $workflowHistory[] = [
                    'action' => 'auto_archived',
                    'user_id' => null, // System action
                    'timestamp' => now()->toISOString(),
                    'status' => 'archived',
                    'reason' => 'Automatically archived after 30 days of completion'
                ];
                $memo->update(['workflow_history' => $workflowHistory]);
                
                $archivedCount++;
            }
            
            $this->info("Successfully auto-archived {$archivedCount} completed memos.");
            
            if ($archivedCount > 0) {
                \Log::info("Auto-archive completed: {$archivedCount} memos archived", [
                    'date' => now()->toDateString(),
                    'archived_count' => $archivedCount
                ]);
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error during auto-archive process: ' . $e->getMessage());
            \Log::error('Auto-archive failed: ' . $e->getMessage(), [
                'date' => now()->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
