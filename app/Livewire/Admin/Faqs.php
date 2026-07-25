<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin management for FAQs — previously seeder-only. A FAQ is either
 * global (faqable null, shown on the FAQ hub) or attached to a treatment
 * (shown on that treatment's page); this list surfaces both with a scope
 * filter.
 */
#[Layout('layouts.app', ['title' => 'FAQs'])]
class Faqs extends Component
{
    use WithPagination;

    #[Url]
    public string $scope = ''; // ''=all, 'global', 'treatment'

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Faq::class);
    }

    public function updatingScope(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function togglePublish(Faq $faq): void
    {
        $this->authorize('publish', $faq);

        $faq->update(['status' => $faq->status === 'published' ? 'draft' : 'published']);
    }

    public function render(): View
    {
        $faqs = Faq::query()
            ->with('faqable')
            ->when($this->scope === 'global', fn ($q) => $q->whereNull('faqable_id'))
            ->when($this->scope === 'treatment', fn ($q) => $q->whereNotNull('faqable_id'))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderBy('sort')
            ->paginate(20);

        return view('livewire.admin.faqs', [
            'faqs' => $faqs,
        ]);
    }
}
