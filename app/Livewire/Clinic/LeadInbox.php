<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Models\Clinic;
use App\Models\LeadAssignment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * A clinic only ever sees leads assigned to it — see
 * docs/09-crm-admin-architecture.md §3. Route-level `clinic.member`
 * middleware (app/Http/Middleware/EnsureClinicMember.php) already restricts
 * access to this clinic; this component further scopes every query to it.
 */
#[Layout('layouts.app', ['title' => 'Lead Inbox'])]
class LeadInbox extends Component
{
    use WithPagination;

    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    public function accept(int $assignmentId): void
    {
        $this->respond($assignmentId, 'accepted');
    }

    public function decline(int $assignmentId): void
    {
        $this->respond($assignmentId, 'declined');
    }

    protected function respond(int $assignmentId, string $status): void
    {
        $assignment = $this->clinic->leadAssignments()->whereKey($assignmentId)->firstOrFail();

        $assignment->update(['status' => $status, 'responded_at' => now()]);

        $assignment->lead->activities()->create([
            'actor_type' => auth()->user()::class,
            'actor_id' => auth()->id(),
            'type' => 'assignment',
            'payload_json' => ['clinic_id' => $this->clinic->id, 'response' => $status],
            'created_at' => now(),
        ]);
    }

    public function render(): View
    {
        $assignments = LeadAssignment::query()
            ->where('clinic_id', $this->clinic->id)
            ->with([
                'lead.primaryTreatment',
                'lead.offers' => fn ($q) => $q->where('clinic_id', $this->clinic->id),
                'lead.appointments' => fn ($q) => $q->where('clinic_id', $this->clinic->id),
                'lead.messages' => fn ($q) => $q->where('clinic_id', $this->clinic->id),
            ])
            ->latest('assigned_at')
            ->paginate(20);

        return view('livewire.clinic.lead-inbox', [
            'assignments' => $assignments,
        ]);
    }
}
