<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public string $reason,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your form was sent back: {$this->submission->reference}",
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.form-submission-rejected',
            with: [
                'submission' => $this->submission,
                'reason'     => $this->reason,
                'showUrl'    => route('admin.forms.show', $this->submission->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
