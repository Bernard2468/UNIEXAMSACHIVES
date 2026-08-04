<?php

namespace App\Mail;

use App\Models\BotConversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the requester the first time a support admin replies, so they hear
 * back even if they've navigated away from the chat widget.
 */
class SupportReplyToUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BotConversation $conversation,
        public User $requester,
        public User $agent,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'An administrator replied to your support request',
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.support.reply-to-user',
            with: [
                'conversation' => $this->conversation,
                'requester'    => $this->requester,
                'agent'        => $this->agent,
                'openUrl'      => route('dashboard') . '?support=' . $this->conversation->id,
            ],
        );
    }
}
