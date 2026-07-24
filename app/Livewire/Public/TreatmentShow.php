<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §3 — the SEO+conversion workhorse.
 */
#[Layout('layouts.public')]
class TreatmentShow extends Component
{
    public Treatment $treatment;

    public function mount(Treatment $treatment): void
    {
        abort_unless($treatment->status === 'published', 404);

        $this->treatment = $treatment;
    }

    public function render(): View
    {
        return view('livewire.public.treatment-show', [
            'clinics' => $this->treatment->clinics()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->limit(6)
                ->get(),
            'faqs' => $this->treatment->faqs()->where('status', 'published')->orderBy('sort')->get(),
            'beforeAfterCases' => $this->treatment->beforeAfterCases()->where('is_published', true)->limit(3)->get(),
            'related' => Treatment::query()
                ->where('status', 'published')
                ->where('id', '!=', $this->treatment->id)
                ->when($this->treatment->category_id, fn ($q) => $q->where('category_id', $this->treatment->category_id))
                ->limit(3)
                ->get(),
        ]);
    }
}
