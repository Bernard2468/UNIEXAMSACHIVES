<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\User;
use App\Services\ResendMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class TestDirectResendService extends Command
{
    protected $signature = 'test:direct-resend-service {email} {--with-attachment} {--subject=Direct ResendService Test}';
    protected $description = 'Test the controller approach using ResendMailService directly';

    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        $withAttachment = $this->option('with-attachment');

        $this->info("🧪 Testing Direct ResendMailService Approach...");
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
            $attachmentPaths = [];
            if ($withAttachment) {
                $this->info("📎 Preparing test attachment...");
                
                // Create a test file
                $testContent = "This is a test attachment for direct ResendService testing.\n\nGenerated on: " . now()->toDateTimeString() . "\nRecipient: {$email}";
                $attachmentPath = 'test_attachments/direct_resend_test_' . time() . '.txt';
                $fullPath = storage_path('app/public/' . $attachmentPath);
                
                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                file_put_contents($fullPath, $testContent);
                
                $attachmentPaths = [
                    [
                        'name' => 'direct_resend_test_attachment.txt',
                        'path' => $attachmentPath,
                        'size' => strlen($testContent),
                        'type' => 'text/plain',
                    ]
                ];
                
                // Prepare for ResendMailService format
                $attachments = [
                    [
                        'filename' => 'direct_resend_test_attachment.txt',
                        'content' => base64_encode($testContent),
                        'type' => 'text/plain',
                    ]
                ];
                
                $this->info("✅ Test attachment created at: {$attachmentPath}");
            }

            // Create test campaign
            $this->info("📋 Creating test campaign...");
            $campaign = EmailCampaign::create([
                'subject' => $subject,
                'message' => "Hello!\n\nThis is a test email using the DIRECT ResendMailService approach (the same method the controller now uses).\n\nTest Details:\n- Sent at: " . now()->toDateTimeString() . "\n- Recipient: {$email}\n- Has attachment: " . ($withAttachment ? 'Yes' : 'No') . "\n- Method: Direct ResendMailService API\n\nIf you received this email, the direct ResendMailService approach is working!\n\nBest regards,\nUniversity Digital Transformation Suite (UDTS)",
                'attachments' => $attachmentPaths,
                'recipient_type' => 'selected',
                'selected_users' => [$user->id],
                'status' => 'sending',
                'scheduled_at' => now(),
                'total_recipients' => 1,
                'created_by' => $user->id,
            ]);

            $this->info("✅ Test campaign created with ID: {$campaign->id}");
            
            // Test the exact same approach the controller uses
            $this->info("📤 Testing ResendMailService directly...");
            
            $resendService = new ResendMailService();
            
            // Generate HTML content (same as controller)
            $htmlContent = view('mails.campaign_simple', [
                'campaign' => $campaign,
                'user' => $user,
                'subject' => $campaign->subject,
                'message' => $campaign->message,
            ])->render();
            
            Log::info("Testing direct ResendMailService approach", [
                'campaign_id' => $campaign->id,
                'user_email' => $user->email,
                'has_attachments' => !empty($attachments),
                'attachments_count' => count($attachments)
            ]);
            
            // Send using ResendMailService directly (same as controller)
            $result = $resendService->sendEmail(
                $user->email,
                $campaign->subject,
                $htmlContent,
                config('mail.from.address'),
                $attachments
            );
            
            if ($result['success']) {
                // Update campaign as sent
                $campaign->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_count' => 1,
                    'failed_count' => 0,
                ]);
                
                $this->info("✅ Email sent successfully using direct ResendMailService!");
                $this->info("📊 Message ID: " . ($result['message_id'] ?? 'N/A'));
                
            } else {
                $campaign->update([
                    'status' => 'failed',
                    'sent_count' => 0,
                    'failed_count' => 1,
                ]);
                
                $this->error("❌ Email failed via ResendMailService!");
                $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
            }
            
            // Clean up test attachment
            if ($withAttachment && isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                $this->info("🗑️  Cleaned up test attachment");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            Log::error("Direct ResendMailService test failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("\n📊 Test Summary:");
        $this->info("📋 Campaign ID: {$campaign->id}");
        $this->info("📊 Final Status: {$campaign->status}");
        $this->info("✅ Sent Count: {$campaign->sent_count}");
        $this->info("❌ Failed Count: {$campaign->failed_count}");
        
        if ($campaign->status === 'sent') {
            $this->info("🎉 SUCCESS: Direct ResendMailService working perfectly!");
            $this->info("📧 Check your email inbox (and spam folder)!");
        } else {
            $this->warn("⚠️  Check logs for detailed error information");
        }

        return 0;
    }
}