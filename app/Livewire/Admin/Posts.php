<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * See docs/04-wireframes.md §11-12 and docs/09-crm-admin-architecture.md §4.
 * Guide and Blog content share this one list, filterable by kind.
 */
#[Layout('layouts.app', ['title' => 'Content'])]
class Posts extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $kind = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Post::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKind(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function togglePublish(Post $post): void
    {
        $this->authorize('publish', $post);

        $post->update($post->status === 'published'
            ? ['status' => 'draft']
            : ['status' => 'published', 'published_at' => $post->published_at ?? now()]);
    }

    public function render(): View
    {
        $posts = Post::query()
            ->with('category')
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->kind !== '', fn ($q) => $q->where('kind', $this->kind))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->category !== '', fn ($q) => $q->where('category_id', $this->category))
            ->latest('updated_at')
            ->paginate(20);

        return view('livewire.admin.posts', [
            'posts' => $posts,
            'categories' => PostCategory::orderBy('sort')->get(),
        ]);
    }
}
