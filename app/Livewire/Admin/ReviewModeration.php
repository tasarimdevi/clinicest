<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Moderation queue for the reviews.moderate permission — see
 * docs/09-crm-admin-architecture.md §4. Every pending row here was
 * either submitted through the patient portal (Review::lead_id set,
 * is_verified derived from a completed TreatmentCase) or seeded by an
 * admin by hand (lead_id null); moderation only ever changes `status`,
 * never `is_verified`.
 */
#[Layout('layouts.app', ['title' => 'Reviews'])]
class ReviewModeration extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Review::class);
    }

    public function approve(int $reviewId): void
    {
        $review = Review::findOrFail($reviewId);
        $this->authorize('moderate', $review);

        $review->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);
    }

    public function reject(int $reviewId): void
    {
        $review = Review::findOrFail($reviewId);
        $this->authorize('moderate', $review);

        $review->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.admin.review-moderation', [
            'reviews' => Review::query()
                ->with(['reviewable', 'reviewerCountry', 'treatment', 'lead'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(20),
        ]);
    }
}
