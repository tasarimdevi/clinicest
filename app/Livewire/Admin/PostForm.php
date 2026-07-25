<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Handles both create and edit — mirrors app/Livewire/Admin/ClinicForm.php,
 * including the mount()-time container-auto-resolution workaround (see
 * that file's docblock) and the update/publish authorization split (see
 * app/Policies/PostPolicy.php).
 */
#[Layout('layouts.app', ['title' => 'Post'])]
class PostForm extends Component
{
    public ?Post $post = null;

    public string $kind = 'blog';

    public bool $is_pillar = false;

    public string $title = '';

    public string $slug = '';

    public ?int $category_id = null;

    public ?int $treatment_id = null;

    public string $excerpt = '';

    public string $body = '';

    public string $hero_image_path = '';

    public string $author_name = '';

    public string $author_credential = '';

    public string $medical_reviewer_name = '';

    public string $medical_reviewer_credential = '';

    public string $reviewed_at = '';

    public string $meta_title = '';

    public string $meta_description = '';

    public string $status = 'draft';

    public function mount(mixed $post = null): void
    {
        $model = match (true) {
            $post instanceof Post => $post,
            $post !== null => Post::findOrFail($post),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Post::class);

        if ($model) {
            $this->post = $model;
            $this->kind = $model->kind;
            // Model::create() doesn't reload DB-level column defaults into
            // the in-memory model, so a post inserted without an explicit
            // is_pillar can have a null attribute here even though the
            // column defaults to false (same footgun as ClinicForm).
            $this->is_pillar = (bool) $model->is_pillar;
            $this->title = $model->getTranslation('title', 'en') ?? '';
            $this->slug = $model->slug;
            $this->category_id = $model->category_id;
            $this->treatment_id = $model->treatment_id;
            $this->excerpt = $model->getTranslation('excerpt', 'en') ?? '';
            $this->body = $model->getTranslation('body', 'en') ?? '';
            $this->hero_image_path = $model->hero_image_path ?? '';
            $this->author_name = $model->author_name ?? '';
            $this->author_credential = $model->author_credential ?? '';
            $this->medical_reviewer_name = $model->medical_reviewer_name ?? '';
            $this->medical_reviewer_credential = $model->medical_reviewer_credential ?? '';
            $this->reviewed_at = $model->reviewed_at?->format('Y-m-d') ?? '';
            $this->meta_title = $model->meta_title ?? '';
            $this->meta_description = $model->meta_description ?? '';
            $this->status = $model->status;
        }
    }

    protected function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(['guide', 'blog'])],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($this->post?->id)],
            'category_id' => ['nullable', Rule::exists(PostCategory::class, 'id')],
            'treatment_id' => ['nullable', Rule::exists(Treatment::class, 'id')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'hero_image_path' => ['nullable', 'string', 'max:2048'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_credential' => ['nullable', 'string', 'max:255'],
            'medical_reviewer_name' => ['nullable', 'string', 'max:255'],
            'medical_reviewer_credential' => ['nullable', 'string', 'max:255'],
            'reviewed_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->post ? 'update' : 'create', $this->post ?? Post::class);

        $validated = $this->validate();
        $validated['is_pillar'] = $this->is_pillar;
        $validated['reviewed_at'] = $this->reviewed_at ?: null;

        if ($this->post) {
            $this->post->update($validated);
        } else {
            // New posts are always created as drafts — going live is a
            // separate, more tightly permissioned action (publish()).
            $validated['status'] = 'draft';
            $this->post = Post::create($validated);
        }

        $this->redirect(route('admin.posts.edit', $this->post), navigate: false);
    }

    public function publish(): void
    {
        $this->authorize('publish', $this->post);

        $this->post->update(['status' => 'published', 'published_at' => $this->post->published_at ?? now()]);

        $this->status = 'published';
        $this->post->refresh();
    }

    public function unpublish(): void
    {
        $this->authorize('publish', $this->post);

        $this->post->update(['status' => 'draft']);

        $this->status = 'draft';
        $this->post->refresh();
    }

    public function render(): View
    {
        return view('livewire.admin.post-form', [
            'categories' => PostCategory::orderBy('sort')->get(),
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
        ]);
    }
}
