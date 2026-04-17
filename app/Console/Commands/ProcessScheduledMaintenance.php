<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemMaintenanceLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class ProcessScheduledMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'maintenance:process-scheduled';

    /**
     * The console command description.
     */
    protected $description = 'Automatically start and end scheduled maintenance based on scheduled times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled maintenance...');

        $now = now();
        $started = 0;
        $completed = 0;

        // Check for maintenance that should start
        $maintenanceToStart = SystemMaintenanceLog::whereIn('status', ['planned', 'notified'])
            ->where('scheduled_start', '<=', $now)
            ->where('scheduled_end', '>', $now)
            ->get();

        foreach ($maintenanceToStart as $maintenance) {
            try {
                // Start the maintenance
                $maintenance->start();

                // If maintenance requires downtime, enable maintenance mode
                if ($maintenance->requires_downtime) {
                    SystemSetting::setMaintenanceMode(true, $maintenance->performed_by ?? null);
                    Log::info('Maintenance mode automatically enabled', [
                        'maintenance_id' => $maintenance->id,
                        'title' => $maintenance->title,
                    ]);
                }

                $started++;
                $this->info("Started maintenance: {$maintenance->title} (ID: {$maintenance->id})");

                Log::info('Maintenance automatically started', [
                    'maintenance_id' => $maintenance->id,
                    'title' => $maintenance->title,
                    'scheduled_start' => $maintenance->scheduled_start,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to start maintenance ID {$maintenance->id}: {$e->getMessage()}");
                Log::error('Failed to start maintenance automatically', [
                    'maintenance_id' => $maintenance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check for maintenance that should end
        $maintenanceToEnd = SystemMaintenanceLog::where('status', 'in_progress')
            ->where('scheduled_end', '<=', $now)
            ->get();

        foreach ($maintenanceToEnd as $maintenance) {
            try {
                // Complete the maintenance
                $maintenance->complete('Automatically completed at scheduled end time');

                // Check if there are any other active maintenance sessions
                $otherActiveMaintenance = SystemMaintenanceLog::where('status', 'in_progress')
                    ->where('id', '!=', $maintenance->id)
                    ->where('requires_downtime', true)
                    ->exists();

                // Only disable maintenance mode if no other maintenance requires downtime
                if (!$otherActiveMaintenance) {
                    SystemSetting::setMaintenanceMode(false, $maintenance->performed_by ?? null);
                    Log::info('Maintenance mode automatically disabled', [
                        'maintenance_id' => $maintenance->id,
                        'title' => $maintenance->title,
                    ]);
                }

                $completed++;
                $this->info("Completed maintenance: {$maintenance->title} (ID: {$maintenance->id})");

                Log::info('Maintenance automatically completed', [
                    'maintenance_id' => $maintenance->id,
                    'title' => $maintenance->title,
                    'scheduled_end' => $maintenance->scheduled_end,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to complete maintenance ID {$maintenance->id}: {$e->getMessage()}");
                Log::error('Failed to complete maintenance automatically', [
                    'maintenance_id' => $maintenance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($started === 0 && $completed === 0) {
            $this->info('No maintenance to process at this time.');
        } else {
            $this->info("Processed: {$started} started, {$completed} completed");
        }

        return Command::SUCCESS;
    }
}

