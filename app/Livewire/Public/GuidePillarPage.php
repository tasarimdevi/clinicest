<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §11. A real Post row (kind=guide,
 * is_pillar=true) rather than a hardcoded page, so it goes through the
 * same admin CMS + EEAT byline as every other article. Renders an honest
 * "not published yet" state instead of a 404 if no pillar post exists —
 * this route is linked from the main footer, so it shouldn't dead-end.
 */
#[Layout('layouts.public')]
class GuidePillarPage extends Component
{
    public function render(): View
    {
        $pillar = Post::query()
            ->where('kind', 'guide')
            ->where('is_pillar', true)
            ->where('status', 'published')
            ->first();

        $clusters = Post::query()
            ->where('kind', 'guide')
            ->where('is_pillar', false)
            ->where('status', 'published')
            ->with('category')
            ->orderBy('title')
            ->get();

        return view('livewire.public.guide-pillar-page', [
            'pillar' => $pillar,
            'clusters' => $clusters->groupBy(fn (Post $p) => $p->category?->getTranslation('name', app()->getLocale()) ?? __('General')),
        ]);
    }
}
