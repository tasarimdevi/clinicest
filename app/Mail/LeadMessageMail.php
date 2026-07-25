<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Clinic;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Clinic $clinic,
        public Message $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A message from '.$this->clinic->getTranslation('name', 'en'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lead-message',
        );
    }
}
