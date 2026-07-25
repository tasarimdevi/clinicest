<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Offers\CreateOffer;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3 "Offer / treatment-plan builder".
 * Only reachable for a lead this clinic has already accepted an assignment
 * for (mirrors LeadInbox's accept/decline flow). Offers are immutable once
 * sent — a revised offer is a new row, not an edit; see the offers
 * migration docblock for why full version-chain tracking (docs/09 §3 says
 * "versioned") is cut from this pass.
 */
#[Layout('layouts.app', ['title' => 'Send Offer'])]
class OfferBuilder extends Component
{
    public Clinic $clinic;

    public Lead $lead;

    public ?int $doctor_id = null;

    public string $title = '';

    public string $treatment_plan = '';

    public string $valid_until = '';

    public bool $includes_hotel = false;

    public bool $includes_transfer = false;

    public ?int $warranty_years = null;

    /** @var array<int, bool> treatment_id => selected */
    public array $selected = [];

    /** @var array<int, string> treatment_id => price in major units (input string) */
    public array $prices = [];

    public bool $sent = false;

    public function mount(Clinic $clinic, Lead $lead): void
    {
        $this->authorize('create', Offer::class);

        abort_unless(
            $clinic->leadAssignments()->where('lead_id', $lead->id)->where('status', 'accepted')->exists(),
            403,
            'This lead has not been accepted by your clinic yet.'
        );

        $this->clinic = $clinic;
        $this->lead = $lead;
        $this->valid_until = now()->addDays(14)->format('Y-m-d');

        foreach ($clinic->treatments as $treatment) {
            $this->prices[$treatment->id] = $treatment->pivot->price_min !== null
                ? number_format($treatment->pivot->price_min / 100, 2, '.', '')
                : '';
        }
    }

    protected function rules(): array
    {
        return [
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'title' => ['required', 'string', 'max:255'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'warranty_years' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }

    public function send(CreateOffer $createOffer): void
    {
        $this->validate();

        $selectedIds = array_keys(array_filter($this->selected));

        if (empty($selectedIds)) {
            $this->addError('selected', __('Select at least one treatment.'));

            return;
        }

        $treatments = $this->clinic->treatments()->whereIn('treatments.id', $selectedIds)->get();

        $breakdown = [];
        $total = 0;

        foreach ($treatments as $treatment) {
            $priceMajor = (float) ($this->prices[$treatment->id] ?? 0);
            $priceMinor = (int) round($priceMajor * 100);
            $total += $priceMinor;

            $breakdown[] = [
                'treatment_id' => $treatment->id,
                'label' => $treatment->getTranslation('name', 'en'),
                'price' => $priceMinor,
            ];
        }

        $this->authorize('create', Offer::class);

        $createOffer->handle($this->lead, $this->clinic, [
            'doctor_id' => $this->doctor_id,
            'title' => $this->title,
            'treatment_plan' => $this->treatment_plan ?: null,
            'price_total' => $total,
            'currency' => $treatments->first()?->pivot->currency ?? 'EUR',
            'breakdown_json' => $breakdown,
            'includes_json' => [
                'hotel' => $this->includes_hotel,
                'transfer' => $this->includes_transfer,
                'warranty_years' => $this->warranty_years,
            ],
            'valid_until' => $this->valid_until,
        ], auth()->user());

        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.clinic.offer-builder', [
            'doctors' => $this->clinic->doctors,
            'treatments' => $this->clinic->treatments,
            'existingOffers' => $this->lead->offers()->where('clinic_id', $this->clinic->id)->latest()->get(),
        ]);
    }
}
