<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use App\Models\Country;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13 and docs/01-product-strategy.md §1 & §5
 * for the positioning/trust-architecture copy this page is built from.
 * No team/press section — this project has neither yet, and the trust
 * principles (see CLAUDE-adjacent docs) forbid fabricating either.
 */
#[Layout('layouts.public')]
class AboutPage extends Component
{
    public function render(): View
    {
        return view('livewire.public.about-page', [
            'verifiedClinicsCount' => Clinic::where('is_active', true)->count(),
            'targetCountriesCount' => Country::where('is_target', true)->count(),
            'reviewsCount' => Review::where('status', 'approved')->count(),
        ]);
    }
}
