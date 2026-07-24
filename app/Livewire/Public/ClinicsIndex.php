<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Enums\VerificationTier;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/04-wireframes.md §4 — the clinic directory. City-scoped routes
 * (/clinics/{city}) aren't wired yet, so city is a filter on this index
 * rather than a separate route, matching what's actually registered.
 */
#[Layout('layouts.public')]
class ClinicsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $treatment = '';

    #[Url]
    public string $city = '';

    #[Url]
    public string $tier = '';

    #[Url]
    public string $search = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $clinics = Clinic::query()
            ->with(['city', 'media'])
            ->where('is_active', true)
            ->when($this->treatment !== '', fn ($q) => $q->whereHas('treatments', fn ($t) => $t->where('treatments.id', $this->treatment)))
            ->when($this->city !== '', fn ($q) => $q->where('city_id', $this->city))
            ->when($this->tier !== '', fn ($q) => $q->where('verification_tier', $this->tier))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->paginate(12);

        return view('livewire.public.clinics-index', [
            'clinics' => $clinics,
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
            'cities' => City::orderBy('name')->get(),
            'tiers' => VerificationTier::cases(),
        ]);
    }
}
