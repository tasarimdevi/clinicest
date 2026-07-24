<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\VerificationTier;
use App\Models\City;
use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/09-crm-admin-architecture.md §4 — clinic directory, verification
 * workflow entry point, and quick featured/active toggles.
 */
#[Layout('layouts.app', ['title' => 'Clinics'])]
class Clinics extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $tier = '';

    #[Url]
    public string $city = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Clinic::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTier(): void
    {
        $this->resetPage();
    }

    public function updatingCity(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Clinic $clinic): void
    {
        $this->authorize('update', $clinic);
        $clinic->update(['is_active' => ! $clinic->is_active]);
    }

    public function toggleFeatured(Clinic $clinic): void
    {
        $this->authorize('update', $clinic);
        $clinic->update(['is_featured' => ! $clinic->is_featured]);
    }

    public function render(): View
    {
        $clinics = Clinic::query()
            ->with('city')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->tier !== '', fn ($q) => $q->where('verification_tier', $this->tier))
            ->when($this->city !== '', fn ($q) => $q->where('city_id', $this->city))
            ->orderBy('slug')
            ->paginate(20);

        return view('livewire.admin.clinics', [
            'clinics' => $clinics,
            'tiers' => VerificationTier::cases(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }
}
