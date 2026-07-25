<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\BeforeAfterCase;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Moderation queue for clinic-submitted before/after cases (reuses
 * reviews.moderate — see BeforeAfterCasePolicy). Only unpublished cases
 * that actually have both photos appear; the seeded, photo-less "pending"
 * cases are already published and shown honestly on public pages, so they
 * never enter this queue.
 *
 * Reject deletes the case and its files rather than keeping a rejected
 * state: there's no rejection_reason/status column on before_after_cases,
 * and keeping un-publishable patient photos around has a real privacy
 * cost. A reason-back-to-clinic flow is a deliberate future addition.
 */
#[Layout('layouts.app', ['title' => 'Before / After'])]
class BeforeAfterModeration extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', BeforeAfterCase::class);
    }

    public function approve(int $caseId): void
    {
        $case = BeforeAfterCase::findOrFail($caseId);
        $this->authorize('moderate', $case);

        $case->update(['is_published' => true]);
    }

    public function reject(int $caseId): void
    {
        $case = BeforeAfterCase::findOrFail($caseId);
        $this->authorize('moderate', $case);

        Storage::disk('public')->delete(array_filter([$case->before_media_path, $case->after_media_path]));
        $case->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.before-after-moderation', [
            'cases' => BeforeAfterCase::query()
                ->with(['clinic', 'treatment', 'patientCountry'])
                ->where('is_published', false)
                ->whereNotNull('before_media_path')
                ->whereNotNull('after_media_path')
                ->latest()
                ->paginate(12),
        ]);
    }
}
