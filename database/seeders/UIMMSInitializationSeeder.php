<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;

class UIMMSInitializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize existing memos for UIMMS
        $this->initializeExistingMemos();
    }

    private function initializeExistingMemos()
    {
        // Get all existing campaigns that don't have memo_status set
        $campaigns = EmailCampaign::whereNull('memo_status')->get();

        foreach ($campaigns as $campaign) {
            // Set default memo status to 'pending' for existing memos
            $campaign->update([
                'memo_status' => 'pending',
                'original_sender_id' => $campaign->created_by,
                'current_assignee_id' => $campaign->created_by, // Initially assigned to creator
            ]);

            // Update existing recipients to be active participants
            $campaign->recipients()->update([
                'is_active_participant' => true,
                'last_activity_at' => now(),
            ]);

            // Add initial workflow history entry
            $campaign->addToWorkflowHistory('created', $campaign->created_by, null, 'Memo initialized for UIMMS');
        }

        $this->command->info("Initialized {$campaigns->count()} existing memos for UIMMS");
    }
}
