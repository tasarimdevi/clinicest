<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin catalog management for treatments — previously seeder-only. Mirrors
 * the Posts list (content.* gating, publish toggle, filters). See
 * docs/09-crm-admin-architecture.md §4.
 */
#[Layout('layouts.app', ['title' => 'Treatments'])]
class Treatments extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Treatment::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function togglePublish(Treatment $treatment): void
    {
        $this->authorize('publish', $treatment);

        $treatment->update(['status' => $treatment->status === 'published' ? 'draft' : 'published']);
    }

    public function render(): View
    {
        $treatments = Treatment::query()
            ->with('category')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->orderBy('sort')
            ->paginate(20);

        return view('livewire.admin.treatments', [
            'treatments' => $treatments,
            'categories' => TreatmentCategory::orderBy('sort')->get(),
        ]);
    }
}
