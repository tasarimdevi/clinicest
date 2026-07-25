<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Site-wide FAQ hub — global FAQs only (faqable_id null). Treatment/clinic/
 * country-scoped FAQs stay on their own pages. See docs/04-wireframes.md §13.
 */
#[Layout('layouts.public')]
class FaqPage extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    public function render(): View
    {
        $faqs = Faq::query()
            ->whereNull('faqable_id')
            ->where('status', 'published')
            ->when($this->search !== '', fn ($q) => $q->where(function ($q) {
                $q->where('question->en', 'like', "%{$this->search}%")
                    ->orWhere('answer->en', 'like', "%{$this->search}%");
            }))
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->orderBy('category')
            ->orderBy('sort')
            ->get();

        return view('livewire.public.faq-page', [
            'faqsByCategory' => $faqs->groupBy(fn (Faq $faq) => $faq->category ?? __('General')),
            'categories' => Faq::query()
                ->whereNull('faqable_id')
                ->where('status', 'published')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }
}
