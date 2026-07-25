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
 * See docs/04-wireframes.md §6 — the doctor directory. Free-text `search`
 * goes through the Meilisearch-backed `doctors` Scout index (name,
 * title/specialty/bio, clinic name) instead of a `full_name LIKE` query —
 * see Doctor::toSearchableArray(). `clinic_is_active` is filtered on the
 * search document itself so the same "active clinics only" rule applies
 * without a relation join.
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
        if ($this->search !== '') {
            $doctors = Doctor::search($this->search)
                ->when($this->clinic !== '', fn ($q) => $q->where('clinic_id', (int) $this->clinic))
                ->paginate(12);
        } else {
            $doctors = Doctor::query()
                ->with('clinic')
                ->whereHas('clinic', fn ($q) => $q->where('is_active', true))
                ->when($this->clinic !== '', fn ($q) => $q->where('clinic_id', $this->clinic))
                ->orderByDesc('is_featured')
                ->orderBy('full_name')
                ->paginate(12);
        }

        return view('livewire.public.doctors-index', [
            'doctors' => $doctors,
            'clinics' => Clinic::where('is_active', true)->orderBy('slug')->get(),
        ]);
    }
}
