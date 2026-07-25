<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\PostCategory;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Manages both taxonomies — treatment categories and post categories —
 * that the Treatment/Post forms reference by dropdown but previously had
 * no way to create. Both are simple (translatable name + slug + sort), so
 * one page with add / rename / delete for each is enough. Deleting a
 * category detaches its treatments/posts (nullOnDelete), it doesn't remove
 * them. Gated on content.* like the rest of content management.
 */
#[Layout('layouts.app', ['title' => 'Categories'])]
class Categories extends Component
{
    /** @var array{en: string, tr: string, slug: string} */
    public array $newTreatment = ['en' => '', 'tr' => '', 'slug' => ''];

    /** @var array{en: string, tr: string, slug: string} */
    public array $newPost = ['en' => '', 'tr' => '', 'slug' => ''];

    /** @var array<int, array<string, string>> id => locale => name */
    public array $treatmentNames = [];

    /** @var array<int, array<string, string>> */
    public array $postNames = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('content.view'), 403);

        $this->hydrateNames();
    }

    protected function hydrateNames(): void
    {
        $locales = config('clinicest.locales.supported', ['en']);

        $this->treatmentNames = TreatmentCategory::orderBy('sort')->get()
            ->mapWithKeys(fn ($c) => [$c->id => collect($locales)->mapWithKeys(fn ($l) => [$l => $c->getTranslations('name')[$l] ?? ''])->all()])
            ->all();

        $this->postNames = PostCategory::orderBy('sort')->get()
            ->mapWithKeys(fn ($c) => [$c->id => collect($locales)->mapWithKeys(fn ($l) => [$l => $c->getTranslations('name')[$l] ?? ''])->all()])
            ->all();
    }

    public function addTreatmentCategory(): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        $data = $this->validate([
            'newTreatment.en' => ['required', 'string', 'max:255'],
            'newTreatment.tr' => ['nullable', 'string', 'max:255'],
            'newTreatment.slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('treatment_categories', 'slug')],
        ])['newTreatment'];

        $category = new TreatmentCategory(['slug' => $data['slug'], 'sort' => TreatmentCategory::max('sort') + 1]);
        $category->setTranslations('name', array_filter(['en' => $data['en'], 'tr' => $data['tr']], fn ($v) => filled($v)));
        $category->save();

        $this->reset('newTreatment');
        $this->hydrateNames();
    }

    public function addPostCategory(): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        $data = $this->validate([
            'newPost.en' => ['required', 'string', 'max:255'],
            'newPost.tr' => ['nullable', 'string', 'max:255'],
            'newPost.slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('post_categories', 'slug')],
        ])['newPost'];

        $category = new PostCategory(['slug' => $data['slug'], 'sort' => PostCategory::max('sort') + 1]);
        $category->setTranslations('name', array_filter(['en' => $data['en'], 'tr' => $data['tr']], fn ($v) => filled($v)));
        $category->save();

        $this->reset('newPost');
        $this->hydrateNames();
    }

    public function saveTreatmentCategory(int $id): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        $category = TreatmentCategory::findOrFail($id);
        $category->setTranslations('name', array_filter($this->treatmentNames[$id] ?? [], fn ($v) => filled($v)));
        $category->save();
    }

    public function savePostCategory(int $id): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        $category = PostCategory::findOrFail($id);
        $category->setTranslations('name', array_filter($this->postNames[$id] ?? [], fn ($v) => filled($v)));
        $category->save();
    }

    public function deleteTreatmentCategory(int $id): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        TreatmentCategory::whereKey($id)->delete();
        $this->hydrateNames();
    }

    public function deletePostCategory(int $id): void
    {
        abort_unless(auth()->user()->can('content.edit'), 403);

        PostCategory::whereKey($id)->delete();
        $this->hydrateNames();
    }

    public function slugifyTreatment(): void
    {
        if ($this->newTreatment['slug'] === '' && $this->newTreatment['en'] !== '') {
            $this->newTreatment['slug'] = Str::slug($this->newTreatment['en']);
        }
    }

    public function slugifyPost(): void
    {
        if ($this->newPost['slug'] === '' && $this->newPost['en'] !== '') {
            $this->newPost['slug'] = Str::slug($this->newPost['en']);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.categories', [
            'treatmentCategories' => TreatmentCategory::orderBy('sort')->get(),
            'postCategories' => PostCategory::orderBy('sort')->get(),
            'canEdit' => auth()->user()->can('content.edit'),
        ]);
    }
}
