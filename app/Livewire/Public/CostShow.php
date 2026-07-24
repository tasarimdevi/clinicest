<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reduced-scope pSEO cost hub — see the country_treatment migration
 * docblock. Only the single-segment /cost/{treatment} page is built;
 * nested /cost/{treatment}/{country} pages are Phase 2+
 * (docs/06-seo-architecture.md) to avoid a thin-content matrix.
 */
#[Layout('layouts.public')]
class CostShow extends Component
{
    public Treatment $treatment;

    public ?int $selected_country_id = null;

    public function mount(Treatment $treatment): void
    {
        abort_unless($treatment->status === 'published', 404);

        $this->treatment = $treatment;
    }

    public function render(): View
    {
        $countryTreatments = $this->treatment->countryTreatments()
            ->with('country')
            ->whereHas('country', fn ($q) => $q->where('is_target', true))
            ->get();

        if ($this->selected_country_id === null && $countryTreatments->isNotEmpty()) {
            $this->selected_country_id = $countryTreatments->first()->country_id;
        }

        return view('livewire.public.cost-show', [
            'countryTreatments' => $countryTreatments,
            'selected' => $countryTreatments->firstWhere('country_id', $this->selected_country_id),
            'clinics' => $this->treatment->clinics()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->limit(6)
                ->get(),
            'faqs' => $this->treatment->faqs()->where('status', 'published')->orderBy('sort')->get(),
        ]);
    }
}
