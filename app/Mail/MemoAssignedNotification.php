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

class MemoAssignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $memo;
    public $assigner;
    public $assignee;
    public $office;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $memo, User $assigner, User $assignee, $office = null)
    {
        $this->memo = $memo;
        $this->assigner = $assigner;
        $this->assignee = $assignee;
        $this->office = $office;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Memo Assignment - ' . $this->memo->subject,
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.memo-assigned-notification',
            with: [
                'memo' => $this->memo,
                'assigner' => $this->assigner,
                'assignee' => $this->assignee,
                'office' => $this->office,
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
