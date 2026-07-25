<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Notifications\AppointmentRespondedTo;
use App\Notifications\ClinicApplicationDecided;
use App\Notifications\LeadAssignedToClinic;
use App\Notifications\NewClinicApplicationSubmitted;
use App\Notifications\OfferRespondedTo;
use App\Notifications\PatientMessageReceived;
use App\Notifications\ReviewPendingModeration;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * docs/09-crm-admin-architecture.md §5's "user-configurable preferences" —
 * every type is listed for every user regardless of role (a clinic_staff
 * will simply never receive a ClinicApplicationDecided notification, so
 * toggling it is a harmless no-op) rather than computing a per-permission
 * subset, which isn't worth the complexity for a handful of checkboxes.
 */
#[Layout('layouts.app', ['title' => 'Notification Settings'])]
class NotificationPreferences extends Component
{
    /** @var array<string, bool> */
    public array $mail = [];

    public bool $digest = true;

    /** @var array<string, string> */
    public array $types = [
        ClinicApplicationDecided::class => 'A clinic application you submitted is decided',
        NewClinicApplicationSubmitted::class => 'A new clinic application is submitted',
        LeadAssignedToClinic::class => 'A lead is assigned to your clinic',
        PatientMessageReceived::class => 'A patient sends a message',
        OfferRespondedTo::class => 'A patient accepts or declines an offer',
        AppointmentRespondedTo::class => 'A patient confirms or cancels an appointment',
        ReviewPendingModeration::class => 'A review needs moderation',
    ];

    public function mount(): void
    {
        $preferences = auth()->user()->notification_preferences ?? [];

        foreach (array_keys($this->types) as $type) {
            $this->mail[$type] = (bool) ($preferences[$type]['mail'] ?? true);
        }

        $this->digest = (bool) ($preferences['digest']['mail'] ?? true);
    }

    public function save(): void
    {
        $preferences = [];

        foreach ($this->mail as $type => $enabled) {
            $preferences[$type] = ['mail' => $enabled];
        }

        $preferences['digest'] = ['mail' => $this->digest];

        auth()->user()->update(['notification_preferences' => $preferences]);

        session()->flash('saved', true);
    }

    public function render(): View
    {
        return view('livewire.settings.notification-preferences');
    }
}
