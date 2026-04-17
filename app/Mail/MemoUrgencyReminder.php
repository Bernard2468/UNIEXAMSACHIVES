<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemoUrgencyReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $memo;
    public $sender;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $memo, User $sender, User $recipient)
    {
        $this->memo = $memo;
        $this->sender = $sender;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚩 Urgency Alert: Pending Memo Requires Attention - ' . $this->memo->subject,
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.memo-urgency-reminder',
            with: [
                'memo' => $this->memo,
                'sender' => $this->sender,
                'recipient' => $this->recipient,
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
        return [];
    }
}

