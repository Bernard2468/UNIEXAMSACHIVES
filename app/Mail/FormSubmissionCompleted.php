<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your form is fully approved: {$this->submission->reference}",
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.form-submission-completed',
            with: [
                'submission' => $this->submission,
                'showUrl'    => route('admin.forms.show', $this->submission->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
