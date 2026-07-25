<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * The portal URL is a Laravel signed URL keyed on the lead's public_id
 * (not the auto-increment id, to avoid enumeration — same reasoning as
 * every other public_id column in this app). The signature itself is
 * the entire access control for the portal: see PatientPortal's
 * docblock for why no separate auth/policy layer exists on top of it.
 */
class PatientPortalLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $portalUrl;

    public function __construct(
        public Lead $lead,
    ) {
        $this->portalUrl = URL::temporarySignedRoute(
            'patient.portal.show',
            now()->addDays(60),
            ['lead' => $lead->public_id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Track your Clinicest request',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.patient-portal-link',
        );
    }
}
