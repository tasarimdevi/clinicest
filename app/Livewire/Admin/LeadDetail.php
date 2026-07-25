<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\Commissions\GenerateCommission;
use App\Actions\Leads\AssignLeadToClinics;
use App\Enums\AppointmentStatus;
use App\Enums\CommissionStatus;
use App\Enums\LeadStatus;
use App\Enums\OfferStatus;
use App\Enums\TreatmentCaseStatus;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\TreatmentCase;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §2 — the 360° lead view: contact +
 * treatment request, activity timeline, assignment, status control, and
 * (once accepted) the treatment case + commission that closes the loop.
 */
#[Layout('layouts.app', ['title' => 'Lead'])]
class LeadDetail extends Component
{
    public Lead $lead;

    /** @var array<int, int> */
    public array $selectedClinicIds = [];

    public ?int $tcClinicId = null;

    public ?int $tcDoctorId = null;

    public string $tcAgreedPrice = '';

    public string $tcCurrency = 'EUR';

    public string $tcArrivalDate = '';

    public string $tcNotes = '';

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

    /**
     * Prefills the create-treatment-case fields from one of the lead's
     * already-accepted offers, so staff aren't retyping a price the
     * clinic already committed to. Not persisted as a relationship — see
     * the treatment_cases migration docblock for why.
     */
    public function loadFromOffer(int $offerId): void
    {
        $offer = $this->lead->offers()->whereKey($offerId)->firstOrFail();

        $this->tcClinicId = $offer->clinic_id;
        $this->tcDoctorId = $offer->doctor_id;
        $this->tcAgreedPrice = number_format($offer->price_total / 100, 2, '.', '');
        $this->tcCurrency = $offer->currency;
    }

    public function createTreatmentCase(): void
    {
        $this->authorize('create', TreatmentCase::class);

        $validated = $this->validate([
            'tcClinicId' => ['required', Rule::exists('clinics', 'id')],
            'tcDoctorId' => ['nullable', Rule::exists('doctors', 'id')],
            'tcAgreedPrice' => ['required', 'numeric', 'min:0'],
            'tcCurrency' => ['required', 'string', 'size:3'],
            'tcArrivalDate' => ['nullable', 'date'],
            'tcNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(
            $this->lead->assignments()->where('clinic_id', $validated['tcClinicId'])->where('status', 'accepted')->exists(),
            403,
            'This clinic has not accepted this lead yet.'
        );

        TreatmentCase::create([
            'lead_id' => $this->lead->id,
            'clinic_id' => $validated['tcClinicId'],
            'doctor_id' => $validated['tcDoctorId'],
            'agreed_price' => (int) round(((float) $validated['tcAgreedPrice']) * 100),
            'currency' => $validated['tcCurrency'],
            'arrival_date' => $validated['tcArrivalDate'] ?: null,
            'notes' => $validated['tcNotes'] ?: null,
            'status' => 'planned',
        ]);

        $this->lead->refresh();
    }

    public function updateTreatmentCaseStatus(string $status): void
    {
        $treatmentCase = $this->lead->treatmentCase;

        abort_if($treatmentCase === null, 404);

        $this->authorize('update', $treatmentCase);

        $treatmentCase->update([
            'status' => $status,
            'completion_date' => $status === 'completed' ? ($treatmentCase->completion_date ?? now()) : $treatmentCase->completion_date,
        ]);

        if ($status === 'completed') {
            $this->lead->update(['status' => LeadStatus::Won]);

            if (! $treatmentCase->commission) {
                app(GenerateCommission::class)->handle($treatmentCase);
            }
        }

        $this->lead->refresh();
    }

    public function updateCommissionStatus(string $status): void
    {
        $commission = $this->lead->treatmentCase?->commission;

        abort_if($commission === null, 404);

        $this->authorize('update', $commission);

        $commission->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? ($commission->paid_at ?? now()) : $commission->paid_at,
        ]);
    }

    public function render(): View
    {
        $treatmentCase = $this->lead->treatmentCase()->with(['clinic', 'doctor', 'commission'])->first();

        return view('livewire.admin.lead-detail', [
            'clinics' => Clinic::query()->where('is_active', true)->orderBy('slug')->get(),
            'statuses' => LeadStatus::cases(),
            'activities' => $this->lead->activities()->latest('created_at')->get(),
            'assignments' => $this->lead->assignments()->with('clinic')->get(),
            'offers' => $this->lead->offers()->with('clinic')->latest()->get(),
            'offerStatuses' => OfferStatus::cases(),
            'appointments' => $this->lead->appointments()->with('clinic')->orderBy('scheduled_at')->get(),
            'appointmentStatuses' => AppointmentStatus::cases(),
            'acceptedAssignments' => $this->lead->assignments()->with('clinic')->where('status', 'accepted')->get(),
            'acceptedOffers' => $this->lead->offers()->with('clinic')->where('status', '!=', 'rejected')->get(),
            'treatmentCase' => $treatmentCase,
            'treatmentCaseStatuses' => TreatmentCaseStatus::cases(),
            'commissionStatuses' => CommissionStatus::cases(),
            'messages' => $this->lead->messages()->with(['clinic', 'sender'])->latest('created_at')->get(),
        ]);
    }
}
