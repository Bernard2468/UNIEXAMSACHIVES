<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the Director of Finance office whenever a procurement memo is
 * approved & unlocked, so Finance can take note of the incoming request.
 *
 * Purely informational — Finance is not being asked to act in the memo thread;
 * they are notified because the approved procurement will land in their office
 * downstream (via the linked PR / PWA forms).
 */
class ProcurementApprovedFinanceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public EmailCampaign $memo;
    public User $approver;
    public User $recipient;
    public string $formLabel;

    public function __construct(EmailCampaign $memo, User $approver, User $recipient, string $formLabel)
    {
        $this->memo = $memo;
        $this->approver = $approver;
        $this->recipient = $recipient;
        $this->formLabel = $formLabel;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Procurement Memo Approved - ' . $this->memo->subject,
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.procurement-approved-finance',
            with: [
                'memo'      => $this->memo,
                'approver'  => $this->approver,
                'recipient' => $this->recipient,
                'formLabel' => $this->formLabel,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
