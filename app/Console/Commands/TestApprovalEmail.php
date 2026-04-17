<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\ResendMailService;
use Illuminate\Support\Str;

class TestApprovalEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:approval-email {email} {--type=approval : Type of email to test (approval or password-update)}';

    /**
     * The console description of the console command.
     *
     * @var string
     */
    protected $description = 'Test the approval or password update email functionality for a specific user email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $emailType = $this->option('type');
        
        $this->line("Testing {$emailType} email functionality for: {$email}");
        $this->line("========================================");
        
        // Find user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $this->info("User found: {$user->first_name} {$user->last_name}");
        $this->line("Current approval status: " . ($user->is_approve ? 'Approved' : 'Not Approved'));
        $this->line("Password changed: " . ($user->password_changed ? 'Yes' : 'No'));
        
        // Test mail configuration
        $this->line("\nChecking mail configuration...");
        $this->line("MAIL_MAILER: " . env('MAIL_MAILER'));
        $this->line("MAIL_HOST: " . env('MAIL_HOST'));
        $this->line("MAIL_FROM_ADDRESS: " . config('mail.from.address'));
        
        // Generate temporary password for testing (firstname + 5 random numbers)
        // $randomNumbers = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        // $temporaryPassword = strtolower($user->first_name) . $randomNumbers;
        // $this->line("\nGenerated temporary password: {$temporaryPassword}");
        
        // Test email sending
        if (env('MAIL_MAILER') == 'resend') {
            $this->line("\nTesting ResendMailService for {$emailType} email...");
            
            try {
                $resendService = new ResendMailService();
                
                if ($emailType === 'password-update') {
                    // Test password update email
                    $htmlContent = view('mails.password_updated', [
                        'firstname' => $user->first_name,
                        'email' => $user->email
                    ])->render();
                    
                    $subject = 'TEST - Password Updated Successfully - Your Account is Now Secure';
                } else {
                    // Test approval email
                    $htmlContent = view('mails.approval', [
                        'firstname' => $user->first_name,
                        'email' => $user->email,
                        // 'temporaryPassword' => $temporaryPassword
                    ])->render();
                    
                    $subject = 'TEST - Account Successfully Approved - Your Login Credentials';
                }
                
                $response = $resendService->sendEmail(
                    $user->email,
                    $subject,
                    $htmlContent,
                    'cug@academicdigital.space'
                );
                
                if ($response['success']) {
                    $this->info("✅ Test {$emailType} email sent successfully!");
                    $this->line("Message ID: " . ($response['message_id'] ?? 'N/A'));
                } else {
                    $this->error("❌ Failed to send test {$emailType} email");
                    $this->line("Error: " . ($response['error'] ?? 'Unknown error'));
                    $this->line("Full response: " . json_encode($response, JSON_PRETTY_PRINT));
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Exception occurred while testing {$emailType} email:");
                $this->line("Error: " . $e->getMessage());
                $this->line("File: " . $e->getFile() . ":" . $e->getLine());
            }
        } else {
            $this->error("MAIL_MAILER is not set to 'resend'. Current value: " . env('MAIL_MAILER'));
        }
        
        $this->line("\nTest completed!");
        return 0;
    }
}
