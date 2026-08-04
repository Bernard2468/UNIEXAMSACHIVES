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
 * Sent to support agents when a user escalates from the bot to a human — both
 * during hours (live) and offline (logged as a ticket for the next shift).
 */
class SupportTicketReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BotConversation $conversation,
        public User $requester,
        public User $agent,
        public bool $online = true,
    ) {
    }

    public function envelope(): Envelope
    {
        $who = trim($this->requester->first_name . ' ' . $this->requester->last_name) ?: 'A user';
        return new Envelope(
            subject: ($this->online ? 'New support chat' : 'New support ticket') . " from {$who}",
            from: config('mail.from.address', 'cug@academicdigital.space'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.support.ticket-received',
            with: [
                'conversation' => $this->conversation,
                'requester'    => $this->requester,
                'agent'        => $this->agent,
                'online'       => $this->online,
                'inboxUrl'     => route('dashboard.support.inbox') . '?c=' . $this->conversation->id,
            ],
        );
    }
}
