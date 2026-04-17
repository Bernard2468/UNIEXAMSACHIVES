<?php

namespace App\Console\Commands;

use App\Mail\CampaignEmail;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

class TestLaravelMailResend extends Command
{
    protected $signature = 'test:laravel-mail-resend {email} {--debug}';
    protected $description = 'Test Laravel Mail with Resend integration to identify exact errors';

    public function handle()
    {
        $email = $this->argument('email');
        $debug = $this->option('debug');

        $this->info("🧪 Testing Laravel Mail + Resend Integration...");
        $this->info("📧 Sending to: {$email}");

        // Show current mail configuration
        $this->info("\n📊 Current Mail Configuration:");
        $this->line("MAIL_MAILER: " . config('mail.default'));
        $this->line("MAIL_FROM_ADDRESS: " . config('mail.from.address'));
        $this->line("MAIL_FROM_NAME: " . config('mail.from.name'));
        
        if ($debug) {
            $this->line("MAIL_HOST: " . config('mail.mailers.resend.host'));
            $this->line("MAIL_PORT: " . config('mail.mailers.resend.port'));
            $this->line("MAIL_ENCRYPTION: " . config('mail.mailers.resend.encryption'));
        }

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
            }

            // Create a test campaign (no attachments first)
            $this->info("📋 Creating test campaign without attachments...");
            $campaign = EmailCampaign::create([
                'subject' => 'Laravel Mail + Resend Test - ' . now()->format('Y-m-d H:i:s'),
                'message' => "Hello!\n\nThis is a test email to debug the Laravel Mail + Resend integration.\n\nSent at: " . now()->toDateTimeString() . "\nRecipient: {$email}\n\nIf you receive this, Laravel Mail + Resend is working!",
                'attachments' => [],
                'recipient_type' => 'selected',
                'selected_users' => [$user->id],
                'status' => 'sending',
                'scheduled_at' => now(),
                'total_recipients' => 1,
                'created_by' => $user->id,
            ]);

            $this->info("✅ Test campaign created with ID: {$campaign->id}");
            
            // Test Laravel Mail + Resend integration
            $this->info("📤 Testing Laravel Mail with Resend...");
            
            Log::info("Testing Laravel Mail + Resend integration", [
                'campaign_id' => $campaign->id,
                'user_email' => $user->email,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'from_address' => config('mail.from.address'),
                ]
            ]);
            
            // This is the exact same call that's failing in the controller
            Mail::to($user->email)->send(new CampaignEmail($campaign, $user));
            
            $this->info("✅ Email sent successfully via Laravel Mail + Resend!");
            
        } catch (Exception $e) {
            $this->error("❌ Laravel Mail + Resend FAILED!");
            $this->error("Error Type: " . get_class($e));
            $this->error("Error Message: " . $e->getMessage());
            
            if ($debug) {
                $this->error("Stack Trace:");
                $this->line($e->getTraceAsString());
            }
            
            Log::error("Laravel Mail + Resend integration test failed", [
                'error_type' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'from_address' => config('mail.from.address'),
                    'resend_config' => config('mail.mailers.resend'),
                ]
            ]);
            
            // Let's try to diagnose common issues
            $this->warn("\n🔍 Diagnosing potential issues...");
            
            // Check if resend mailer is properly configured
            $resendConfig = config('mail.mailers.resend');
            if (empty($resendConfig)) {
                $this->error("❌ Resend mailer not found in config!");
            } else {
                $this->info("✅ Resend mailer configuration found");
            }
            
            // Check if from address is valid
            $fromAddress = config('mail.from.address');
            if (empty($fromAddress) || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                $this->error("❌ Invalid from address: " . $fromAddress);
            } else {
                $this->info("✅ From address valid: " . $fromAddress);
            }
            
            // Check if it's a template issue
            if (strpos($e->getMessage(), 'template') !== false || strpos($e->getMessage(), 'view') !== false) {
                $this->warn("🔍 This might be a template/view issue");
                $this->warn("Check if resources/views/mails/campaign_simple.blade.php exists and is valid");
            }
            
            // Check if it's a Resend API issue
            if (strpos($e->getMessage(), 'resend') !== false || strpos($e->getMessage(), 'api') !== false) {
                $this->warn("🔍 This might be a Resend API issue");
                $this->warn("Check if RESEND_API_KEY is correct in .env");
            }
            
            // Check if it's an SMTP issue
            if (strpos($e->getMessage(), 'smtp') !== false || strpos($e->getMessage(), 'connection') !== false) {
                $this->warn("🔍 This might be an SMTP connection issue");
                $this->warn("Check MAIL_HOST, MAIL_PORT, MAIL_ENCRYPTION in .env");
            }
            
            return 1;
        }

        $this->info("\n🎉 SUCCESS: Laravel Mail + Resend integration is working!");
        $this->info("📧 Check your email inbox!");

        return 0;
    }
}