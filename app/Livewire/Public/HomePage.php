<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use App\Models\Treatment;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §1 for the full section-by-section spec.
 */
#[Layout('layouts.public')]
class HomePage extends Component
{
    public function render()
    {
        return view('livewire.public.home-page', [
            'featuredTreatments' => Treatment::query()
                ->where('status', 'published')
                ->orderByDesc('is_featured')
                ->orderBy('sort')
                ->limit(8)
                ->get(),
            'featuredClinics' => Clinic::query()
                ->with(['city', 'media'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->limit(6)
                ->get(),
        ]);
    }
}
