<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Clinic;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * The gap this closes: a clinic replying to a lead already emails the
 * patient (LeadMessageMail), but nothing ever told clinic staff a patient
 * replied back through the portal — see SendLeadMessage's docblock.
 */
class PatientMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message, public Clinic $clinic) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New message from a patient',
            'body' => str($this->message->body)->limit(120)->toString(),
            'url' => route('clinic.leads', $this->clinic),
        ];
    }
}
