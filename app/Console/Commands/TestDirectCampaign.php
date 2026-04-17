<?php

namespace App\Console\Commands;

use App\Mail\CampaignEmail;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class TestDirectCampaign extends Command
{
    protected $signature = 'test:direct-campaign {email} {--with-attachment} {--subject=Direct Campaign Test}';
    protected $description = 'Test the new direct campaign email system';

    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        $withAttachment = $this->option('with-attachment');

        $this->info("🧪 Testing Direct Campaign Email System...");
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
                $testContent = "This is a test attachment for direct campaign email testing.\n\nGenerated on: " . now()->toDateTimeString() . "\nRecipient: {$email}";
                $attachmentPath = 'test_attachments/direct_campaign_test_' . time() . '.txt';
                $fullPath = storage_path('app/public/' . $attachmentPath);
                
                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                file_put_contents($fullPath, $testContent);
                
                $attachments = [
                    [
                        'name' => 'direct_campaign_test_attachment.txt',
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
                'message' => "Hello!\n\nThis is a test email from the NEW direct campaign system of the University Digital Transformation Suite (UDTS).\n\nWe have completely rewritten the advance communication system to work reliably.\n\nTest Details:\n- Sent at: " . now()->toDateTimeString() . "\n- Recipient: {$email}\n- Has attachment: " . ($withAttachment ? 'Yes' : 'No') . "\n- Method: Direct Mail (like broadcast system)\n\nIf you received this email, the NEW system is working correctly!\n\nBest regards,\nUniversity Digital Transformation Suite (UDTS)",
                'attachments' => $attachments,
                'recipient_type' => 'selected',
                'selected_users' => [$user->id],
                'status' => 'sending',
                'scheduled_at' => now(),
                'total_recipients' => 1,
                'created_by' => $user->id,
            ]);

            $this->info("✅ Test campaign created with ID: {$campaign->id}");
            
            // Send email directly using the new method
            $this->info("📤 Sending email directly...");
            
            Log::info("Testing direct campaign email", [
                'campaign_id' => $campaign->id,
                'user_email' => $user->email,
                'has_attachments' => !empty($attachments)
            ]);
            
            // Send using Laravel Mail directly (the new way)
            Mail::to($user->email)->send(new CampaignEmail($campaign, $user));
            
            // Update campaign as sent
            $campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_count' => 1,
                'failed_count' => 0,
            ]);
            
            $this->info("✅ Email sent successfully using direct method!");
            $this->info("📊 Campaign ID: {$campaign->id}");
            $this->info("📊 Status: {$campaign->status}");
            
            // Clean up test attachment
            if ($withAttachment && isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                $this->info("🗑️  Cleaned up test attachment");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            Log::error("Direct campaign email test failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("\n🎉 SUCCESS: Direct campaign email sent!");
        $this->info("📧 Check your email inbox (and spam folder)!");
        $this->info("📊 Campaign ID: {$campaign->id}");
        $this->info("📊 Final Status: {$campaign->status}");

        return 0;
    }
}