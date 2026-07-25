<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Enums\VerificationTier;
use App\Models\Clinic;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13 — static content page, no filters/state.
 * Trust-proof numbers below are computed from real rows, never hardcoded,
 * per the project's no-fabrication rule.
 */
#[Layout('layouts.public')]
class HowItWorksPage extends Component
{
    public function render(): View
    {
        return view('livewire.public.how-it-works-page', [
            'verifiedClinicsCount' => Clinic::where('is_active', true)->count(),
            'publishedTreatmentsCount' => Treatment::where('status', 'published')->count(),
            'tiers' => VerificationTier::cases(),
        ]);
    }
}
