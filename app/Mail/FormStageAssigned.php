<?php

namespace App\Mail;

use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormStageAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public User $recipient,
        public User $sender,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Form awaiting your action: {$this->submission->reference} ({$this->submission->form_code})",
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.form-stage-assigned',
            with: [
                'submission' => $this->submission,
                'recipient'  => $this->recipient,
                'sender'     => $this->sender,
                'showUrl'    => route('admin.forms.show', $this->submission->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
