<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\CampaignDeliveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:send-scheduled {--dry-run : List due memos without sending}';

    protected $description = 'Deliver memos/campaigns scheduled for later whose scheduled_at has now arrived';

    public function handle(CampaignDeliveryService $delivery): int
    {
        // status = 'scheduled' AND scheduled_at <= now  (EmailCampaign scope)
        $due = EmailCampaign::scheduledForSending()->orderBy('scheduled_at')->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled memos are due.');
            return self::SUCCESS;
        }

        $this->info("Found {$due->count()} scheduled memo(s) due for delivery.");

        foreach ($due as $campaign) {
            $label = "#{$campaign->id} \"{$campaign->subject}\" (scheduled {$campaign->scheduled_at})";

            if ($this->option('dry-run')) {
                $this->line("  [dry-run] would send {$label}");
                continue;
            }

            try {
                $delivery->deliver($campaign);
                $this->info("  Sent {$label}");
            } catch (Throwable $e) {
                // Mark failed so a transient error never re-fires every minute.
                $campaign->update(['status' => 'failed']);
                Log::error("Scheduled campaign {$campaign->id} delivery failed: " . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("  FAILED {$label}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
