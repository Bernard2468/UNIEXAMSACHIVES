<?php

namespace App\Mail;

use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Nudge email sent by the `forms:nudge-stale` command to assignees who have
 * been holding a form for too long. The Cc list includes the office head
 * only when the form has crossed the escalation threshold (7+ days) so the
 * head isn't pinged about every minor delay.
 */
class FormStaleNudge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public User $recipient,
        public int $staleDays,
        public bool $escalated = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->escalated
            ? "Escalation: form {$this->submission->reference} stuck for {$this->staleDays} days"
            : "Reminder: form {$this->submission->reference} awaiting your action ({$this->staleDays} days)";

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.form-stale-nudge',
            with: [
                'submission' => $this->submission,
                'recipient'  => $this->recipient,
                'staleDays'  => $this->staleDays,
                'escalated'  => $this->escalated,
                'showUrl'    => route('admin.forms.show', $this->submission->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
