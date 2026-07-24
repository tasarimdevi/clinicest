<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §5 — certificates and full case galleries
 * are omitted (not modeled yet, docs/10-roadmap.md Phase 2) rather than
 * shown with placeholder or fabricated content.
 */
#[Layout('layouts.public')]
class ClinicShow extends Component
{
    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        abort_unless($clinic->is_active, 404);

        $this->clinic = $clinic;
    }

    public function render(): View
    {
        return view('livewire.public.clinic-show', [
            'treatments' => $this->clinic->treatments()->where('is_available', true)->get(),
            'doctors' => $this->clinic->doctors()->orderByDesc('is_featured')->get(),
            'reviews' => $this->clinic->reviews()->where('status', 'approved')->latest()->limit(3)->get(),
            'beforeAfterCases' => $this->clinic->beforeAfterCases()->where('is_published', true)->limit(3)->get(),
            'relatedClinics' => Clinic::query()
                ->where('is_active', true)
                ->where('id', '!=', $this->clinic->id)
                ->where('city_id', $this->clinic->city_id)
                ->limit(3)
                ->get(),
        ]);
    }
}
