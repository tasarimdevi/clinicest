<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Clinic;
use App\Models\Review;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/04-wireframes.md §11. Doctor reviews aren't broken out here —
 * they live on the doctor's own profile page.
 */
#[Layout('layouts.public')]
class ReviewsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $clinic = '';

    #[Url]
    public string $treatment = '';

    #[Url]
    public string $rating = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $reviews = Review::query()
            ->with(['reviewerCountry', 'treatment'])
            ->where('reviewable_type', Clinic::class)
            ->where('status', 'approved')
            ->when($this->clinic !== '', fn ($q) => $q->where('reviewable_id', $this->clinic))
            ->when($this->treatment !== '', fn ($q) => $q->where('treatment_id', $this->treatment))
            ->when($this->rating !== '', fn ($q) => $q->where('rating', $this->rating))
            ->latest()
            ->paginate(10);

        return view('livewire.public.reviews-index', [
            'reviews' => $reviews,
            'clinics' => Clinic::where('is_active', true)->orderBy('slug')->get(),
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
        ]);
    }
}
