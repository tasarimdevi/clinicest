<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Country;
use App\Models\Treatment;
use App\Services\CostEstimatorService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * See docs/07-ai-architecture.md §2.3. Standalone version of the pairing
 * picker — unlike the CostShow calculator (docs/06-seo-architecture.md's
 * /cost/{treatment} page, which starts from a fixed treatment), this page
 * lets a visitor pick both treatment and country from scratch, so it can
 * be linked from anywhere (home, lead confirmation).
 */
#[Layout('layouts.public')]
class AiCostEstimator extends Component
{
    #[Url]
    public ?int $treatment_id = null;

    #[Url]
    public ?int $country_id = null;

    public function mount(): void
    {
        if ($this->treatment_id !== null
            && ! Treatment::where('id', $this->treatment_id)->where('status', 'published')->exists()) {
            $this->treatment_id = null;
        }

        if ($this->country_id !== null
            && ! Country::where('id', $this->country_id)->where('is_target', true)->exists()) {
            $this->country_id = null;
        }
    }

    public function render(CostEstimatorService $estimator): View
    {
        $treatment = $this->treatment_id ? Treatment::find($this->treatment_id) : null;
        $country = $this->country_id ? Country::find($this->country_id) : null;

        return view('livewire.public.ai-cost-estimator', [
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
            'countries' => Country::where('is_target', true)->orderBy('name')->get(),
            'selectedTreatment' => $treatment,
            'selectedCountry' => $country,
            'estimate' => $treatment ? $estimator->estimate($treatment, $country) : null,
        ]);
    }
}
