<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §12 (article template).
 */
#[Layout('layouts.public')]
class BlogShow extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_unless($post->kind === 'blog' && $post->status === 'published', 404);

        $this->post = $post;
    }

    public function render(): View
    {
        $related = Post::query()
            ->where('kind', 'blog')
            ->where('status', 'published')
            ->where('id', '!=', $this->post->id)
            ->when($this->post->category_id, fn ($q) => $q->where('category_id', $this->post->category_id))
            ->limit(3)
            ->get();

        return view('livewire.public.blog-show', [
            'related' => $related,
        ]);
    }
}
