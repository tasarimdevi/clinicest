<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §2 — the treatments hub.
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
        $treatments = Treatment::query()
            ->where('status', 'published')
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('sort')
            ->get();

        return view('livewire.public.treatments-index', [
            'treatments' => $treatments,
            'categories' => TreatmentCategory::orderBy('sort')->get(),
        ]);
    }
}
