<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicApplicationDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Clinic $clinic,
    ) {}

    public function envelope(): Envelope
    {
        $approved = $this->clinic->application_status === 'approved';

        return new Envelope(
            subject: $approved
                ? 'Your Clinicest application has been approved'
                : 'An update on your Clinicest application',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.clinic-application-decision',
        );
    }
}
