<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/09-crm-admin-architecture.md §4 — doctor directory, credential
 * verification is handled via the certificates/media pipeline (Phase 2+),
 * this covers CRUD + featured toggle for now.
 */
#[Layout('layouts.app', ['title' => 'Doctors'])]
class Doctors extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $clinic = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Doctor::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClinic(): void
    {
        $this->resetPage();
    }

    public function toggleFeatured(Doctor $doctor): void
    {
        $this->authorize('update', $doctor);
        $doctor->update(['is_featured' => ! $doctor->is_featured]);
    }

    public function render(): View
    {
        $doctors = Doctor::query()
            ->with('clinic')
            ->when($this->search !== '', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%"))
            ->when($this->clinic !== '', fn ($q) => $q->where('clinic_id', $this->clinic))
            ->orderBy('full_name')
            ->paginate(20);

        return view('livewire.admin.doctors', [
            'doctors' => $doctors,
            'clinics' => Clinic::orderBy('slug')->get(),
        ]);
    }
}
