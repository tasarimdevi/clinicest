<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $notifications,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Clinicest notification summary');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.notification-digest');
    }
}
