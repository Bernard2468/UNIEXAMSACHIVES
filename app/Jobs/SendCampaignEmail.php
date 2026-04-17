<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Services\ResendMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // If campaign no longer exists, skip
            if (!$this->campaign->exists) {
                return;
            }
            // Update campaign status
            $this->campaign->update(['status' => 'sending']);

            $recipients = $this->campaign->recipients()
                                       ->where('status', 'pending')
                                       ->with('user')
                                       ->get();

            $sentCount = 0;
            $failedCount = 0;

            // Use Resend service to send campaign email
            $resendService = new ResendMailService();
            
            Log::info("Starting campaign email send", [
                'campaign_id' => $this->campaign->id,
                'total_recipients' => count($recipients),
                'campaign_attachments' => $this->campaign->attachments
            ]);

            foreach ($recipients as $recipient) {
                try {
                    // Skip if user is not approved
                    if (!$recipient->user->is_approve) {
                        $recipient->markAsFailed('User account not approved');
                        $failedCount++;
                        continue;
                    }

                    Log::info("Sending campaign email to individual recipient", [
                        'campaign_id' => $this->campaign->id,
                        'user_id' => $recipient->user->id,
                        'user_email' => $recipient->user->email,
                    ]);
                    
                    // Generate HTML content for this recipient
                    $htmlContent = view('mails.campaign', [
                        'campaign' => $this->campaign,
                        'user' => $recipient->user,
                        'subject' => $this->campaign->subject,
                        'message' => $this->campaign->message,
                    ])->render();

                    // Prepare attachments for Resend API
                    $attachments = [];
                    if ($this->campaign->attachments && is_array($this->campaign->attachments)) {
                        foreach ($this->campaign->attachments as $attachment) {
                            $filePath = storage_path('app/public/' . $attachment['path']);
                            if (file_exists($filePath)) {
                                $fileContent = file_get_contents($filePath);
                                if ($fileContent !== false) {
                                    $attachments[] = [
                                        'filename' => $attachment['name'],
                                        'content' => base64_encode($fileContent),
                                        'type' => $attachment['type'] ?? mime_content_type($filePath),
                                    ];
                                }
                            } else {
                                Log::warning("Attachment file not found: {$filePath}");
                            }
                        }
                    }
                    
                                         // Send email directly using sendEmail method (not sendCampaignEmails)
                     Log::info("About to send email via Resend", [
                         'recipient_email' => $recipient->user->email,
                         'subject' => $this->campaign->subject,
                         'attachments_count' => count($attachments),
                         'attachments_data' => $attachments
                     ]);
                     
                     try {
                         $fromName = optional($this->campaign->creator)->first_name && optional($this->campaign->creator)->last_name
                             ? trim($this->campaign->creator->first_name . ' ' . $this->campaign->creator->last_name)
                             : (config('mail.from.name') ?: 'CUG');
                         $result = $resendService->sendEmail(
                             $recipient->user->email,
                             $this->campaign->subject,
                             $htmlContent,
                             config('mail.from.address'),
                             $attachments,
                             0,
                             $fromName
                         );
                     } catch (Exception $e) {
                         Log::error("Exception in Resend sendEmail", [
                             'error' => $e->getMessage(),
                             'trace' => $e->getTraceAsString()
                         ]);
                         $result = ['success' => false, 'error' => $e->getMessage()];
                     }
                     
                     Log::info("Resend sendEmail result", [
                         'result' => $result,
                         'success' => $result['success'] ?? false
                     ]);

                    if ($result['success']) {
                        // Mark as sent
                        $recipient->markAsSent();
                        $sentCount++;
                        
                        Log::info("Campaign email sent successfully via Resend", [
                            'user_id' => $recipient->user->id,
                            'email' => $recipient->user->email,
                            'message_id' => $result['message_id'] ?? null
                        ]);
                    } else {
                        // Mark as failed
                        $error = $result['error'] ?? 'Unknown error';
                        $recipient->markAsFailed('Resend API error: ' . $error);
                        $failedCount++;
                        
                        Log::error("Failed to send campaign email via Resend", [
                            'user_id' => $recipient->user->id,
                            'email' => $recipient->user->email,
                            'error' => $error
                        ]);
                    }

                    // Add small delay to prevent overwhelming the mail server
                    usleep(500000); // 0.5 second delay (respects Resend rate limits)

                } catch (Exception $e) {
                    Log::error("Failed to send campaign email to user {$recipient->user->id}: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $recipient->markAsFailed($e->getMessage());
                    $failedCount++;
                }
            }

            // Update campaign final status and counts
            if ($this->campaign->fresh()) {
                $this->campaign->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                ]);
            }

            Log::info("Campaign {$this->campaign->id} completed. Sent: {$sentCount}, Failed: {$failedCount}");

        } catch (Exception $e) {
            Log::error("Campaign {$this->campaign->id} failed: " . $e->getMessage());
            
            // Mark campaign as failed
            if ($this->campaign->fresh()) {
                $this->campaign->update([
                    'status' => 'failed',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Campaign email job failed for campaign {$this->campaign->id}: " . $exception->getMessage());
        
        $this->campaign->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['email-campaign', 'campaign:' . $this->campaign->id];
    }
}