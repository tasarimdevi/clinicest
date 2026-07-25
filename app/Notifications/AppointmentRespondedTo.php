<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentRespondedTo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Appointment '.$this->appointment->status->value,
            'body' => "{$this->appointment->lead->full_name} {$this->appointment->status->value} their appointment.",
            'url' => route('clinic.leads', $this->appointment->clinic),
        ];
    }
}
