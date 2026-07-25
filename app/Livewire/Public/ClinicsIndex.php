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
 *
 * Free-text `search` goes through the Meilisearch-backed `clinics` Scout
 * index (typo-tolerant, matches name/about/city/treatment names) instead
 * of a `name LIKE` query — see Clinic::toSearchableArray(). The other
 * filters are applied as Scout `where()` calls against the facets
 * configured in config/scout.php, not as a separate Eloquent pass, so a
 * search + filter combination is resolved by Meilisearch in one query.
 * With no search term, filtering falls back to the plain Eloquent path
 * (cheaper, and keeps `is_featured`/`rating_avg` ordering exact — Scout
 * result order is Meilisearch's ranking, not a DB sort).
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
        if ($this->search !== '') {
            $clinics = Clinic::search($this->search)
                ->when($this->treatment !== '', fn ($q) => $q->where('treatment_ids', (int) $this->treatment))
                ->when($this->city !== '', fn ($q) => $q->where('city_id', (int) $this->city))
                ->when($this->tier !== '', fn ($q) => $q->where('verification_tier', $this->tier))
                ->paginate(12);
        } else {
            $clinics = Clinic::query()
                ->with(['city', 'media'])
                ->where('is_active', true)
                ->when($this->treatment !== '', fn ($q) => $q->whereHas('treatments', fn ($t) => $t->where('treatments.id', $this->treatment)))
                ->when($this->city !== '', fn ($q) => $q->where('city_id', $this->city))
                ->when($this->tier !== '', fn ($q) => $q->where('verification_tier', $this->tier))
                ->orderByDesc('is_featured')
                ->orderByDesc('rating_avg')
                ->paginate(12);
        }

        return view('livewire.public.clinics-index', [
            'clinics' => $clinics,
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
            'cities' => City::orderBy('name')->get(),
            'tiers' => VerificationTier::cases(),
        ]);
    }
}
