<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\BeforeAfterCase;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\Treatment;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §1 for the full section-by-section spec.
 *
 * Every trust figure below is derived from real, published data (active
 * clinics, approved reviews, published before/after cases) so the homepage
 * never fabricates a count — a guiding constraint in docs/10-roadmap.md §3.
 * When a figure has no data yet (e.g. no approved reviews), the view falls
 * back to an honest neutral state rather than a made-up number.
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
                ->limit(6)
                ->get(),

            'featuredClinics' => Clinic::query()
                ->with(['city', 'media'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->limit(6)
                ->get(),

            // Real before/after cases for the interactive gallery — published
            // only, most recent first. The slider component itself falls back
            // to a "photos pending" state per case (see before-after-card).
            'beforeAfterCases' => BeforeAfterCase::query()
                ->with(['clinic', 'treatment'])
                ->where('is_published', true)
                ->latest('id')
                ->limit(8)
                ->get(),

            // Approved clinic reviews for the testimonial wall.
            'reviews' => Review::query()
                ->with(['reviewerCountry', 'treatment'])
                ->where('reviewable_type', Clinic::class)
                ->where('status', 'approved')
                ->latest('moderated_at')
                ->limit(6)
                ->get(),

            // A few doctors from active clinics for the editorial strip.
            'doctors' => Doctor::query()
                ->with('clinic')
                ->whereHas('clinic', fn ($q) => $q->where('is_active', true))
                ->orderByDesc('is_featured')
                ->limit(4)
                ->get(),

            'stats' => $this->trustStats(),
        ]);
    }

    /**
     * Hard, real trust numbers for the hero badge row and stats band.
     *
     * @return array{clinics:int, reviews:int, avgRating:float|null, beforeAfter:int}
     */
    protected function trustStats(): array
    {
        $approved = Review::where('reviewable_type', Clinic::class)->where('status', 'approved');

        return [
            'clinics' => Clinic::where('is_active', true)->count(),
            'reviews' => (clone $approved)->count(),
            'avgRating' => (clone $approved)->count() > 0
                ? round((float) (clone $approved)->avg('rating'), 1)
                : null,
            'beforeAfter' => BeforeAfterCase::where('is_published', true)->count(),
        ];
    }
}
