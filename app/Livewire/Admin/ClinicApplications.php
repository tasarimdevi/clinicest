<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Mail\ClinicApplicationDecisionMail;
use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3 — the admin side of self-
 * onboarding. Reuses the `verify` ability (clinics.verify) rather than a
 * new permission: reviewing an application and setting a verification
 * tier are the same kind of trust decision, and moderator already holds
 * clinics.verify for exactly this reason.
 */
#[Layout('layouts.app', ['title' => 'Clinic Applications'])]
class ClinicApplications extends Component
{
    /** @var array<int, int> */
    public array $rejecting = [];

    /** @var array<int, string> */
    public array $rejectReason = [];

    public function mount(): void
    {
        // Gated on clinics.verify, not the broader clinics.view (viewAny) —
        // this page is specifically the verification-review queue, and
        // moderator (clinics.verify only, no clinics.view) is the role
        // that exists to do exactly this.
        abort_unless(auth()->user()->can('clinics.verify'), 403);
    }

    public function startReject(int $clinicId): void
    {
        $this->rejecting[$clinicId] = true;
    }

    public function approve(Clinic $clinic): void
    {
        $this->authorize('verify', $clinic);

        $clinic->update([
            'application_status' => 'approved',
            'verification_tier' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'is_active' => true,
        ]);

        Mail::to($clinic->owner?->email ?? $clinic->email)->send(new ClinicApplicationDecisionMail($clinic->fresh()));
    }

    public function reject(Clinic $clinic): void
    {
        $this->authorize('verify', $clinic);

        $clinic->update([
            'application_status' => 'rejected',
            'rejection_reason' => $this->rejectReason[$clinic->id] ?? null,
        ]);

        Mail::to($clinic->owner?->email ?? $clinic->email)->send(new ClinicApplicationDecisionMail($clinic->fresh()));

        unset($this->rejecting[$clinic->id]);
    }

    public function render(): View
    {
        return view('livewire.admin.clinic-applications', [
            'applications' => Clinic::query()
                ->where('application_status', 'pending')
                ->with(['city', 'owner', 'documents'])
                ->oldest('applied_at')
                ->get(),
        ]);
    }
}
