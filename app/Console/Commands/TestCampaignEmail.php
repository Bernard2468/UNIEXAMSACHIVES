<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Jobs\SendCampaignEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCampaignEmail extends Command
{
    protected $signature = 'test:campaign-email {email} {--with-attachment} {--subject=Test Campaign Email}';
    protected $description = 'Test the campaign email system with a single recipient';

    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        $withAttachment = $this->option('with-attachment');

        $this->info("🧪 Testing Campaign Email System...");
        $this->info("📧 Sending to: {$email}");
        $this->info("📝 Subject: {$subject}");

        try {
            // Find or create a test user
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->info("👤 Creating test user for {$email}...");
                $user = User::create([
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'is_approve' => true,
                    'is_admin' => false,
                ]);
            } else if (!$user->is_approve) {
                $this->info("✅ Approving user {$email}...");
                $user->update(['is_approve' => true]);
            }

            // Prepare attachments if requested
            $attachments = [];
            if ($withAttachment) {
                $this->info("📎 Preparing test attachment...");
                
                // Create a test file
                $testContent = "This is a test attachment for campaign email testing.\n\nGenerated on: " . now()->toDateTimeString() . "\nRecipient: {$email}";
                $attachmentPath = 'test_attachments/campaign_test_' . time() . '.txt';
                $fullPath = storage_path('app/public/' . $attachmentPath);
                
                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                file_put_contents($fullPath, $testContent);
                
                $attachments = [
                    [
                        'name' => 'campaign_test_attachment.txt',
                        'path' => $attachmentPath,
                        'size' => strlen($testContent),
                        'type' => 'text/plain',
                    ]
                ];
                
                $this->info("✅ Test attachment created at: {$attachmentPath}");
            }

            // Create test campaign
            $this->info("📋 Creating test campaign...");
            $campaign = EmailCampaign::create([
                'subject' => $subject,
                'message' => "Hello!\n\nThis is a test email from the University Digital Transformation Suite (UDTS) System's advance communication system.\n\nWe are testing the email functionality with attachments and queue processing.\n\nTest Details:\n- Sent at: " . now()->toDateTimeString() . "\n- Recipient: {$email}\n- Has attachment: " . ($withAttachment ? 'Yes' : 'No') . "\n\nIf you received this email, the system is working correctly!\n\nBest regards,\nUniversity Digital Transformation Suite (UDTS)",
                'attachments' => $attachments,
                'recipient_type' => 'selected',
                'selected_users' => [$user->id],
                'status' => 'draft',
                'scheduled_at' => now(),
                'total_recipients' => 1,
                'created_by' => $user->id,
            ]);

            // Create recipient record
            $recipient = EmailCampaignRecipient::create([
                'comm_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            $this->info("✅ Test campaign created with ID: {$campaign->id}");
            
            // Dispatch the job
            $this->info("🚀 Dispatching campaign email job...");
            SendCampaignEmail::dispatch($campaign);
            
            $this->info("✅ Campaign email job dispatched successfully!");
            $this->info("📊 Campaign ID: {$campaign->id}");
            
            // Monitor the campaign for a few seconds
            $this->info("⏳ Monitoring campaign status...");
            
            for ($i = 0; $i < 30; $i++) { // Monitor for 30 seconds
                sleep(1);
                $campaign->refresh();
                
                $status = $campaign->status;
                $this->line("Status: {$status} (checking " . ($i + 1) . "/30)");
                
                if ($status === 'sent') {
                    $this->info("🎉 Campaign completed successfully!");
                    $this->info("✅ Sent count: {$campaign->sent_count}");
                    $this->info("❌ Failed count: {$campaign->failed_count}");
                    break;
                } elseif ($status === 'failed') {
                    $this->error("❌ Campaign failed!");
                    
                    // Check recipient error
                    $recipient->refresh();
                    if ($recipient->error_message) {
                        $this->error("Error: {$recipient->error_message}");
                    }
                    break;
                }
            }
            
            if ($campaign->fresh()->status === 'sending') {
                $this->warn("⚠️  Campaign still in 'sending' status after 30 seconds");
                $this->warn("💡 Check logs and queue worker status");
            }
            
            // Clean up test attachment
            if ($withAttachment && isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                $this->info("🗑️  Cleaned up test attachment");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            Log::error("Campaign email test failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("\n📋 Test Summary:");
        $this->line("Campaign ID: {$campaign->id}");
        $this->line("Final Status: {$campaign->status}");
        $this->line("Sent Count: {$campaign->sent_count}");
        $this->line("Failed Count: {$campaign->failed_count}");
        
        if ($campaign->status === 'sent') {
            $this->info("🎉 SUCCESS: Check your email inbox (and spam folder)!");
        } else {
            $this->warn("⚠️  Check logs for detailed error information");
        }

        return 0;
    }
}