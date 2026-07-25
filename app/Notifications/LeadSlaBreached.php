<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Clinic;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Fills the "SLA breach" slot docs/09-crm-admin-architecture.md §5 called
 * for and the notification system left open. Sent to the sales team when a
 * clinic misses its response window — the body reflects whether the lead
 * was auto-reassigned or now needs manual attention.
 */
class LeadSlaBreached extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Lead $lead,
        public Clinic $lapsedClinic,
        public ?Clinic $reassignedTo = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $lapsedName = $this->lapsedClinic->getTranslation('name', 'en');

        $body = $this->reassignedTo
            ? "{$lapsedName} missed its SLA on {$this->lead->full_name} — auto-reassigned to {$this->reassignedTo->getTranslation('name', 'en')}."
            : "{$lapsedName} missed its SLA on {$this->lead->full_name} — no eligible clinic to auto-reassign, needs manual attention.";

        return [
            'title' => 'Lead SLA breached',
            'body' => $body,
            'url' => route('admin.leads.show', $this->lead),
        ];
    }
}
