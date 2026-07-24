<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/04-wireframes.md §6 — the doctor directory.
 */
#[Layout('layouts.public')]
class DoctorsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $clinic = '';

    #[Url]
    public string $search = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $doctors = Doctor::query()
            ->with('clinic')
            ->whereHas('clinic', fn ($q) => $q->where('is_active', true))
            ->when($this->clinic !== '', fn ($q) => $q->where('clinic_id', $this->clinic))
            ->when($this->search !== '', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%"))
            ->orderByDesc('is_featured')
            ->orderBy('full_name')
            ->paginate(12);

        return view('livewire.public.doctors-index', [
            'doctors' => $doctors,
            'clinics' => Clinic::where('is_active', true)->orderBy('slug')->get(),
        ]);
    }
}
