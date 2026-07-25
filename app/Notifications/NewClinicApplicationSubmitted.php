<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Operational, potentially-frequent — database only (bell) + swept into
 * the daily digest (see SendNotificationDigest), never an immediate email.
 * See ClinicApplicationDecided for the one-off/immediate counterpart.
 */
class NewClinicApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Clinic $clinic) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New clinic application',
            'body' => "{$this->clinic->getTranslation('name', 'en')} applied to join Clinicest.",
            'url' => route('admin.clinics.applications'),
        ];
    }
}
