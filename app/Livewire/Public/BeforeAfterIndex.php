<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\BeforeAfterCase;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §10. Also reachable pre-filtered from a
 * treatment page as ?treatment={id}.
 */
#[Layout('layouts.public')]
class BeforeAfterIndex extends Component
{
    #[Url]
    public string $treatment = '';

    public function render(): View
    {
        $cases = BeforeAfterCase::query()
            ->with(['clinic', 'treatment'])
            ->where('is_published', true)
            ->when($this->treatment !== '', fn ($q) => $q->where('treatment_id', $this->treatment))
            ->latest()
            ->get();

        return view('livewire.public.before-after-index', [
            'cases' => $cases,
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
        ]);
    }
}
