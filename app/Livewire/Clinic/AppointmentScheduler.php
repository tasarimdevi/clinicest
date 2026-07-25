<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Appointments\RequestAppointment;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3 "Appointment requests: confirm/
 * reschedule remote consults & on-site visits". Reachable for any lead this
 * clinic has already accepted (same precondition as the offer builder) —
 * unlike an offer, an appointment can be a pre-offer consult, so it isn't
 * gated on an offer existing.
 */
#[Layout('layouts.app', ['title' => 'Appointments'])]
class AppointmentScheduler extends Component
{
    public Clinic $clinic;

    public Lead $lead;

    public ?int $doctor_id = null;

    public string $type = 'remote_consult';

    public string $scheduled_at = '';

    public string $timezone = 'Europe/Istanbul';

    public string $meeting_url = '';

    public string $notes = '';

    public function mount(Clinic $clinic, Lead $lead): void
    {
        $this->authorize('create', Appointment::class);

        abort_unless(
            $clinic->leadAssignments()->where('lead_id', $lead->id)->where('status', 'accepted')->exists(),
            403,
            'This lead has not been accepted by your clinic yet.'
        );

        $this->clinic = $clinic;
        $this->lead = $lead;
        $this->scheduled_at = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
    }

    protected function rules(): array
    {
        return [
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'type' => ['required', Rule::enum(AppointmentType::class)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'timezone' => ['required', 'timezone'],
            'meeting_url' => ['nullable', 'url', 'required_if:type,remote_consult'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function request(RequestAppointment $requestAppointment): void
    {
        $validated = $this->validate();

        $requestAppointment->handle($this->lead, $this->clinic, [
            'doctor_id' => $validated['doctor_id'],
            'type' => $validated['type'],
            'scheduled_at' => $validated['scheduled_at'],
            'timezone' => $validated['timezone'],
            'meeting_url' => $validated['meeting_url'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ], auth()->user());

        $this->reset(['doctor_id', 'meeting_url', 'notes']);
        $this->scheduled_at = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $appointment = $this->clinic->appointments()->where('lead_id', $this->lead->id)->whereKey($appointmentId)->firstOrFail();

        $this->authorize('update', $appointment);

        $appointment->update(['status' => AppointmentStatus::from($status)]);
    }

    public function render(): View
    {
        return view('livewire.clinic.appointment-scheduler', [
            'doctors' => $this->clinic->doctors,
            'types' => AppointmentType::cases(),
            'statuses' => AppointmentStatus::cases(),
            'appointments' => $this->clinic->appointments()->where('lead_id', $this->lead->id)->orderBy('scheduled_at')->get(),
        ]);
    }
}
