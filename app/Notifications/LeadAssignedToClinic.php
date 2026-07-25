<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Clinic;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeadAssignedToClinic extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead, public Clinic $clinic) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New lead assigned',
            'body' => "{$this->lead->full_name} was assigned to your clinic.",
            'url' => route('clinic.leads', $this->clinic),
        ];
    }
}
