<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * A guide cluster article — links up to the pillar and sideways to
 * siblings in the same category, per docs/04-wireframes.md §11.
 */
#[Layout('layouts.public')]
class GuideArticleShow extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_unless($post->kind === 'guide' && $post->status === 'published', 404);

        $this->post = $post;
    }

    public function render(): View
    {
        $pillar = Post::where('kind', 'guide')->where('is_pillar', true)->where('status', 'published')->first();

        $siblings = Post::query()
            ->where('kind', 'guide')
            ->where('is_pillar', false)
            ->where('status', 'published')
            ->where('id', '!=', $this->post->id)
            ->when($this->post->category_id, fn ($q) => $q->where('category_id', $this->post->category_id))
            ->limit(4)
            ->get();

        return view('livewire.public.guide-article-show', [
            'pillar' => $pillar,
            'siblings' => $siblings,
        ]);
    }
}
