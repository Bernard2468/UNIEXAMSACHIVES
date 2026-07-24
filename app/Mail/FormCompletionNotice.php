<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Courtesy "it's done — no action needed" notice sent to the signers of a
 * form's key stages once it reaches final completion (e.g. the Director of
 * Finance and the Registrar on a Payment Requisition, once disbursement is
 * confirmed).
 *
 * Distinct from FormSubmissionCompleted, which is the requester-facing
 * "your form is fully approved" email — the wording here is for someone who
 * signed an earlier stage, not the person who raised the form.
 */
class FormCompletionNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public string $headline,
        public string $bodyMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Now complete: {$this->submission->reference}",
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.form-completion-notice',
            with: [
                'submission'  => $this->submission,
                'headline'    => $this->headline,
                'bodyMessage' => $this->bodyMessage,
                'showUrl'     => route('admin.forms.show', $this->submission->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
