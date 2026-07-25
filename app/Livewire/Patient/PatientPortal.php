<?php

declare(strict_types=1);

namespace App\Livewire\Patient;

use App\Actions\Messages\SendLeadMessage;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §2 — the "Patient (lead)" role:
 * "created implicitly from a lead; magic-link/portal access to their
 * request, offers, messages, appointments, review." This is the whole
 * access-control model: the route this component sits behind is
 * Laravel-signed (see routes/web/patient.php and PatientPortalLinkMail),
 * so there is no separate auth guard, policy, or account — the signature
 * itself is the credential, exactly matching "magic-link" access. This
 * intentionally does NOT cover the docs' separate "Registered Patient"
 * role (full account, saved clinics, multiple requests) — that needs a
 * real registration/password system that doesn't exist anywhere in this
 * app yet (see Login.php's own docblock) and is a distinct future item.
 *
 * Document visibility is deliberately left out: DocumentPolicy's
 * authorization is User-permission-based, and a magic-link visitor has
 * no User/session at all — extending it safely is a separate piece of
 * work, not bundled into this pass.
 */
#[Layout('layouts.public')]
class PatientPortal extends Component
{
    public Lead $lead;

    /** @var array<int, string> clinic_id => draft reply text */
    public array $replyBodies = [];

    public int $reviewRating = 5;

    public string $reviewTitle = '';

    public string $reviewBody = '';

    public bool $reviewSubmitted = false;

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;

        // Closes a gap that existed everywhere else in the app: nothing
        // ever transitioned an Offer from sent -> viewed before now,
        // since only the patient's own visit can honestly signal that.
        $this->lead->offers()->where('status', 'sent')->update(['status' => 'viewed']);
    }

    public function acceptOffer(int $offerId): void
    {
        $offer = $this->lead->offers()->whereKey($offerId)->firstOrFail();

        abort_unless(in_array($offer->status->value, ['sent', 'viewed'], true), 403);

        $offer->update(['status' => 'accepted']);

        $this->logActivity('offer_accepted', ['offer_id' => $offer->id]);
    }

    public function rejectOffer(int $offerId): void
    {
        $offer = $this->lead->offers()->whereKey($offerId)->firstOrFail();

        abort_unless(in_array($offer->status->value, ['sent', 'viewed'], true), 403);

        $offer->update(['status' => 'rejected']);

        $this->logActivity('offer_rejected', ['offer_id' => $offer->id]);
    }

    public function confirmAppointment(int $appointmentId): void
    {
        $appointment = $this->lead->appointments()->whereKey($appointmentId)->firstOrFail();

        abort_unless($appointment->status->value === 'requested', 403);

        $appointment->update(['status' => 'confirmed']);

        $this->logActivity('appointment_confirmed', ['appointment_id' => $appointment->id]);
    }

    public function cancelAppointment(int $appointmentId): void
    {
        $appointment = $this->lead->appointments()->whereKey($appointmentId)->firstOrFail();

        abort_unless(in_array($appointment->status->value, ['requested', 'confirmed'], true), 403);

        $appointment->update(['status' => 'cancelled']);

        $this->logActivity('appointment_cancelled', ['appointment_id' => $appointment->id]);
    }

    public function sendMessage(int $clinicId, SendLeadMessage $sendLeadMessage): void
    {
        abort_unless(
            $this->lead->assignments()->where('clinic_id', $clinicId)->where('status', 'accepted')->exists(),
            403
        );

        $this->validate([
            "replyBodies.$clinicId" => ['required', 'string', 'max:5000'],
        ]);

        $clinic = Clinic::findOrFail($clinicId);

        $sendLeadMessage->handle($this->lead, $clinic, [
            'direction' => 'inbound',
            'channel' => 'web',
            'body' => $this->replyBodies[$clinicId],
        ], $this->lead);

        unset($this->replyBodies[$clinicId]);
    }

    public function submitReview(): void
    {
        $treatmentCase = $this->lead->treatmentCase;

        abort_unless($treatmentCase && $treatmentCase->status->value === 'completed', 403);

        abort_if(
            Review::where('lead_id', $this->lead->id)
                ->where('reviewable_type', Clinic::class)
                ->where('reviewable_id', $treatmentCase->clinic_id)
                ->exists(),
            403,
            'You have already reviewed this clinic.'
        );

        $validated = $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewTitle' => ['nullable', 'string', 'max:255'],
            'reviewBody' => ['required', 'string', 'max:2000'],
        ]);

        Review::create([
            'reviewable_type' => Clinic::class,
            'reviewable_id' => $treatmentCase->clinic_id,
            'lead_id' => $this->lead->id,
            'reviewer_name' => $this->lead->full_name,
            'reviewer_country_id' => $this->lead->country_id,
            'rating' => $validated['reviewRating'],
            'title' => $validated['reviewTitle'] ?: null,
            'body' => $validated['reviewBody'],
            'treatment_id' => $this->lead->primary_treatment_id,
            // Genuinely verified — this lead has a completed treatment
            // case with this exact clinic, not just a self-reported claim.
            'is_verified' => true,
            'status' => 'pending',
        ]);

        $this->reviewSubmitted = true;
    }

    protected function logActivity(string $event, array $payload = []): void
    {
        $this->lead->activities()->create([
            'actor_type' => Lead::class,
            'actor_id' => $this->lead->id,
            'type' => 'system',
            'payload_json' => ['event' => $event, ...$payload],
            'created_at' => now(),
        ]);
    }

    public function render(): View
    {
        $treatmentCase = $this->lead->treatmentCase;

        $canReview = $treatmentCase
            && $treatmentCase->status->value === 'completed'
            && ! Review::where('lead_id', $this->lead->id)
                ->where('reviewable_type', Clinic::class)
                ->where('reviewable_id', $treatmentCase->clinic_id)
                ->exists();

        return view('livewire.patient.portal', [
            'offers' => $this->lead->offers()->with('clinic')->latest()->get(),
            'appointments' => $this->lead->appointments()->with('clinic')->orderBy('scheduled_at')->get(),
            'messagesByClinic' => $this->lead->messages()->with(['clinic', 'sender'])->oldest('created_at')->get()->groupBy('clinic_id'),
            'acceptedAssignments' => $this->lead->assignments()->with('clinic')->where('status', 'accepted')->get(),
            'treatmentCase' => $treatmentCase?->load('clinic'),
            'canReview' => $canReview,
        ]);
    }
}
