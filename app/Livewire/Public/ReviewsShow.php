<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/04-wireframes.md §11 — clinic reviews page. Rating breakdown
 * bars and AI-summarized themes aren't built yet (docs/07-ai-architecture.md
 * §2.5 is Phase 2+); the real rating distribution is shown instead.
 */
#[Layout('layouts.public')]
class ReviewsShow extends Component
{
    use WithPagination;

    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        abort_unless($clinic->is_active, 404);

        $this->clinic = $clinic;
    }

    public function render(): View
    {
        $reviews = $this->clinic->reviews()
            ->with(['reviewerCountry', 'treatment'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        $breakdown = $this->clinic->reviews()
            ->where('status', 'approved')
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return view('livewire.public.reviews-show', [
            'reviews' => $reviews,
            'breakdown' => $breakdown,
        ]);
    }
}
