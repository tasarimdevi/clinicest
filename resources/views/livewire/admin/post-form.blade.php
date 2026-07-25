<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Basic info') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Kind') }}</label>
                        <select wire:model="kind" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="guide">{{ __('Guide') }}</option>
                            <option value="blog">{{ __('Blog') }}</option>
                        </select>
                        @error('kind') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Category') }}</label>
                        <select wire:model="category_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->getTranslation('name', 'en') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Title') }}</label>
                        <input type="text" wire:model="title" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('title') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Slug') }}</label>
                        <input type="text" wire:model="slug" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm">
                        @error('slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Related treatment') }}</label>
                        <select wire:model="treatment_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($treatments as $t)
                                <option value="{{ $t->id }}">{{ $t->getTranslation('name', 'en') }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($kind === 'guide')
                        <div class="flex items-center">
                            <label class="mt-6 flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" wire:model="is_pillar" class="rounded border-ink-300">
                                {{ __('This is the Guide pillar page (only one should be)') }}
                            </label>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Excerpt') }}</label>
                        <textarea wire:model="excerpt" rows="2" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                        @error('excerpt') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Hero image URL (optional)') }}</label>
                        <input type="text" wire:model="hero_image_path" placeholder="https://" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('hero_image_path') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Body (HTML)') }}</label>
                        <textarea wire:model="body" rows="14" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-xs"></textarea>
                        @error('body') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Byline & EEAT') }}</h2>
                <p class="mt-1 text-xs text-ink-500">{{ __('Leave the reviewer fields blank unless a real dentist reviewed this — the site never fabricates a review.') }}</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Author name') }}</label>
                        <input type="text" wire:model="author_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Author credential') }}</label>
                        <input type="text" wire:model="author_credential" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Medical reviewer name') }}</label>
                        <input type="text" wire:model="medical_reviewer_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Reviewer credential') }}</label>
                        <input type="text" wire:model="medical_reviewer_credential" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Reviewed on') }}</label>
                        <input type="date" wire:model="reviewed_at" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('reviewed_at') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('SEO') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Meta title') }}</label>
                        <input type="text" wire:model="meta_title" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Meta description') }}</label>
                        <textarea wire:model="meta_description" rows="2" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
                {{ $post ? __('Save changes') : __('Create post') }}
            </x-button>
        </form>
    </div>

    <div class="space-y-6">
        @if ($post)
            @can('publish', $post)
                <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('Publishing') }}</h2>
                    <p class="mt-1 text-xs text-ink-500">
                        {{ __('Current status:') }}
                        <span class="font-semibold {{ $status === 'published' ? 'text-success-600' : 'text-ink-600' }}">
                            {{ $status === 'published' ? __('Published') : __('Draft') }}
                        </span>
                    </p>
                    @if ($status === 'published')
                        <x-button wire:click="unpublish" variant="ghost" size="sm" class="mt-4 w-full">{{ __('Unpublish') }}</x-button>
                    @else
                        <x-button wire:click="publish" size="sm" class="mt-4 w-full">{{ __('Publish') }}</x-button>
                    @endif
                    @if ($post->published_at)
                        <p class="mt-3 text-xs text-ink-500">{{ __('First published') }} {{ $post->published_at->format('d M Y') }}</p>
                    @endif
                </div>
            @else
                <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('Publishing') }}</h2>
                    <p class="mt-2 text-sm text-ink-700">{{ $status === 'published' ? __('Published') : __('Draft') }}</p>
                    <p class="mt-3 text-xs text-ink-400">{{ __('You have content.edit but not content.publish — you can edit this post but not take it live.') }}</p>
                </div>
            @endcan
        @else
            <div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-6 text-sm text-ink-500">
                {{ __('Save the post first to unlock publishing.') }}
            </div>
        @endif
    </div>
</div>
