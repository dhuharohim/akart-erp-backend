<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ETicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $eventName,
        public string $seriesName,
        public string $registrationNumber,
        public string $ticketUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your E-Ticket - '.$this->registrationNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.e-ticket',
        );
    }
}
