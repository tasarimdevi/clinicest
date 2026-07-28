<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

/**
 * See docs/04-wireframes.md §2 — the treatments hub. Free-text `search`
 * goes through the Meilisearch-backed `treatments` Scout index (name,
 * summary, category name) instead of a `name LIKE` query — see
 * Treatment::toSearchableArray(). Scout's result order is Meilisearch's
 * relevance ranking rather than the `sort` column, which only applies
 * on the unfiltered/no-search path.
 */
#[Layout('layouts.public')]
class TreatmentsIndex extends Component
{
    #[Url]
    public string $category = '';

    #[Url]
    public string $search = '';

    public function render(): View
    {
        if ($this->search !== '') {
            try {
                $treatments = Treatment::search($this->search)
                    ->when($this->category !== '', fn ($q) => $q->where('category_id', (int) $this->category))
                    ->get();
            } catch (Throwable $e) {
                // Meilisearch unreachable — degrade to a plain DB query
                // rather than 500ing the page; see docs/06-seo-architecture.md.
                Log::warning('Scout search unavailable, falling back to DB query', ['exception' => $e->getMessage()]);
                $treatments = Treatment::query()
                    ->where('status', 'published')
                    ->where('name', 'like', "%{$this->search}%")
                    ->when($this->category !== '', fn ($q) => $q->where('category_id', (int) $this->category))
                    ->orderBy('sort')
                    ->get();
            }
        } else {
            $treatments = Treatment::query()
                ->where('status', 'published')
                ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
                ->orderBy('sort')
                ->get();
        }

        return view('livewire.public.treatments-index', [
            'treatments' => $treatments,
            'categories' => TreatmentCategory::orderBy('sort')->get(),
        ]);
    }
}
