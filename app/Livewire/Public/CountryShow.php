<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use App\Models\Country;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reduced-scope pSEO country hub — see the country_treatment migration
 * docblock. Only the single-segment /countries/{country} page is built;
 * nested /countries/{country}/{treatment} pages are Phase 2+
 * (docs/06-seo-architecture.md) to avoid a thin-content matrix.
 */
#[Layout('layouts.public')]
class CountryShow extends Component
{
    public Country $country;

    public function mount(Country $country): void
    {
        abort_unless($country->is_target, 404);

        $this->country = $country;
    }

    public function render(): View
    {
        $countryTreatments = $this->country->countryTreatments()
            ->with('treatment')
            ->whereHas('treatment', fn ($q) => $q->where('status', 'published'))
            ->get();

        $clinics = Clinic::query()
            ->with(['city', 'media'])
            ->where('is_active', true)
            ->when(
                $this->country->primary_language,
                fn ($q) => $q->whereJsonContains('languages_json', $this->country->primary_language)
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->limit(6)
            ->get();

        return view('livewire.public.country-show', [
            'countryTreatments' => $countryTreatments,
            'clinics' => $clinics,
            'faqs' => $this->country->faqs()->where('status', 'published')->orderBy('sort')->get(),
        ]);
    }
}
