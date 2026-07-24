<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §6. Education/experience/awards timelines,
 * certificates, and case galleries aren't modeled yet (docs/10-roadmap.md
 * Phase 2) — bio + credentials actually on the model are shown honestly
 * rather than padded with placeholder timeline entries.
 */
#[Layout('layouts.public')]
class DoctorShow extends Component
{
    public Doctor $doctor;

    public function mount(Doctor $doctor): void
    {
        $this->doctor = $doctor->load('clinic');
    }

    public function render(): View
    {
        return view('livewire.public.doctor-show', [
            'related' => Doctor::query()
                ->where('clinic_id', $this->doctor->clinic_id)
                ->where('id', '!=', $this->doctor->id)
                ->limit(3)
                ->get(),
        ]);
    }
}
