<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\ClinicApplicationDecisionMail;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * A one-off, time-critical decision — unlike the operational notifications
 * in this namespace (lead assigned, message received, etc.), this stays
 * immediate ('mail' + 'database' together, not digest-only). Reuses the
 * existing ClinicApplicationDecisionMail markdown template verbatim
 * rather than rebuilding the content as a MailMessage.
 */
class ClinicApplicationDecided extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Clinic $clinic) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable instanceof User && $notifiable->wantsEmailFor(self::class)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new ClinicApplicationDecisionMail($this->clinic))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $approved = $this->clinic->application_status === 'approved';

        return [
            'title' => $approved ? 'Application approved' : 'Application update',
            'body' => $approved
                ? "{$this->clinic->getTranslation('name', 'en')} has been approved and is now live."
                : "{$this->clinic->getTranslation('name', 'en')}'s application was not approved.",
            'url' => route('clinic.dashboard', $this->clinic),
        ];
    }
}
