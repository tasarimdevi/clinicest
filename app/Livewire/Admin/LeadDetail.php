<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\Leads\AssignLeadToClinics;
use App\Enums\AppointmentStatus;
use App\Enums\LeadStatus;
use App\Enums\OfferStatus;
use App\Models\Clinic;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §2 — the 360° lead view: contact +
 * treatment request, activity timeline, assignment, status control.
 */
#[Layout('layouts.app', ['title' => 'Lead'])]
class LeadDetail extends Component
{
    public Lead $lead;

    /** @var array<int, int> */
    public array $selectedClinicIds = [];

    public function mount(Lead $lead): void
    {
        $this->authorize('view', $lead);

        $this->lead = $lead;
        $this->selectedClinicIds = $lead->assignments()->pluck('clinic_id')->all();
    }

    public function assign(AssignLeadToClinics $assignLeadToClinics): void
    {
        $this->authorize('assign', $this->lead);

        $this->validate([
            'selectedClinicIds' => ['required', 'array', 'min:1'],
        ]);

        $this->lead = $assignLeadToClinics->handle($this->lead, $this->selectedClinicIds, auth()->user());
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->lead);

        $this->lead->update(['status' => LeadStatus::from($status)]);

        $this->lead->activities()->create([
            'actor_type' => auth()->user()::class,
            'actor_id' => auth()->id(),
            'type' => 'status_change',
            'payload_json' => ['status' => $status],
            'created_at' => now(),
        ]);

        $this->lead->refresh();
    }

    public function updateOfferStatus(int $offerId, string $status): void
    {
        $offer = $this->lead->offers()->whereKey($offerId)->firstOrFail();

        $this->authorize('update', $offer);

        $offer->update(['status' => OfferStatus::from($status)]);
    }

    public function updateAppointmentStatus(int $appointmentId, string $status): void
    {
        $appointment = $this->lead->appointments()->whereKey($appointmentId)->firstOrFail();

        $this->authorize('update', $appointment);

        $appointment->update(['status' => AppointmentStatus::from($status)]);
    }

    public function render(): View
    {
        return view('livewire.admin.lead-detail', [
            'clinics' => Clinic::query()->where('is_active', true)->orderBy('slug')->get(),
            'statuses' => LeadStatus::cases(),
            'activities' => $this->lead->activities()->latest('created_at')->get(),
            'assignments' => $this->lead->assignments()->with('clinic')->get(),
            'offers' => $this->lead->offers()->with('clinic')->latest()->get(),
            'offerStatuses' => OfferStatus::cases(),
            'appointments' => $this->lead->appointments()->with('clinic')->orderBy('scheduled_at')->get(),
            'appointmentStatuses' => AppointmentStatus::cases(),
        ]);
    }
}
