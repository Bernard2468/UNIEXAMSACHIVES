<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, User $user)
    {
        $this->campaign = $campaign;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.campaign_simple',
            with: [
                'subject' => $this->campaign->subject,
                'message' => $this->campaign->message,
                'user' => $this->user,
                'campaign' => $this->campaign,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->campaign->attachments && is_array($this->campaign->attachments)) {
            Log::info("Processing attachments for CampaignEmail", [
                'campaign_id' => $this->campaign->id,
                'user_email' => $this->user->email,
                'attachments_count' => count($this->campaign->attachments)
            ]);
            
            foreach ($this->campaign->attachments as $attachment) {
                try {
                    if (!isset($attachment['path']) || !isset($attachment['name'])) {
                        Log::warning("Invalid attachment structure in CampaignEmail", [
                            'campaign_id' => $this->campaign->id,
                            'attachment' => $attachment
                        ]);
                        continue;
                    }
                    
                    $filePath = storage_path('app/public/' . $attachment['path']);
                    
                    if (file_exists($filePath)) {
                        $attachments[] = Attachment::fromPath($filePath)
                                                   ->as($attachment['name'])
                                                   ->withMime($attachment['type'] ?? 'application/octet-stream');
                                                   
                        Log::info("Attachment added to CampaignEmail", [
                            'campaign_id' => $this->campaign->id,
                            'filename' => $attachment['name'],
                            'path' => $filePath,
                            'exists' => true
                        ]);
                    } else {
                        Log::error("Attachment file not found in CampaignEmail", [
                            'campaign_id' => $this->campaign->id,
                            'filename' => $attachment['name'],
                            'path' => $filePath,
                            'exists' => false
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error("Error processing attachment in CampaignEmail", [
                        'campaign_id' => $this->campaign->id,
                        'attachment' => $attachment,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        Log::info("Final attachments for CampaignEmail", [
            'campaign_id' => $this->campaign->id,
            'user_email' => $this->user->email,
            'final_attachments_count' => count($attachments)
        ]);

        return $attachments;
    }

    /**
     * Get the tags for the message.
     */
    public function tags(): array
    {
        return ['campaign', 'campaign-' . $this->campaign->id];
    }

    /**
     * Set the priority of the message.
     */
    public function priority($level = 3): int
    {
        return $level; // Allow custom priority or default to 3
    }
}