<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §12. `/blog/{category}` from the documented
 * IA is collapsed into a query-string filter on this one index — same
 * simplification already used for the clinics/doctors/treatments
 * directories — rather than a separate nested route.
 */
#[Layout('layouts.public')]
class BlogIndex extends Component
{
    #[Url]
    public string $category = '';

    public function render(): View
    {
        $featured = Post::query()
            ->where('kind', 'blog')
            ->where('status', 'published')
            ->latest('published_at')
            ->first();

        $posts = Post::query()
            ->where('kind', 'blog')
            ->where('status', 'published')
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->orderByDesc('published_at')
            ->get();

        return view('livewire.public.blog-index', [
            'featured' => $this->category === '' ? $featured : null,
            'posts' => $posts,
            'categories' => PostCategory::whereHas('posts', fn ($q) => $q->where('kind', 'blog')->where('status', 'published'))
                ->orderBy('sort')
                ->get(),
        ]);
    }
}
