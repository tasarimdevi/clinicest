<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by title…') }}"
                   class="w-full max-w-xs rounded-md border-ink-300 text-sm">
            <select wire:model.live="kind" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('Guide + Blog') }}</option>
                <option value="guide">{{ __('Guide') }}</option>
                <option value="blog">{{ __('Blog') }}</option>
            </select>
            <select wire:model.live="status" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="published">{{ __('Published') }}</option>
            </select>
            <select wire:model.live="category" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->getTranslation('name', app()->getLocale()) }}</option>
                @endforeach
            </select>
        </div>
        @can('create', \App\Models\Post::class)
            <x-button :href="route('admin.posts.create')" as="a" size="sm">{{ __('Add post') }}</x-button>
        @endcan
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="px-4 py-3">{{ __('Kind') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Updated') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($posts as $post)
                    <tr wire:key="post-{{ $post->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">
                            {{ $post->getTranslation('title', app()->getLocale()) }}
                            @if ($post->is_pillar)
                                <span class="ml-1 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-medium text-gold-600">{{ __('Pillar') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ ucfirst($post->kind) }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $post->category?->getTranslation('name', app()->getLocale()) ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @can('publish', $post)
                                <button wire:click="togglePublish({{ $post->id }})"
                                        @class(['text-xs font-semibold', 'text-success-600' => $post->status === 'published', 'text-ink-400' => $post->status !== 'published'])>
                                    {{ $post->status === 'published' ? __('Published') : __('Draft') }}
                                </button>
                            @else
                                <span class="text-xs font-semibold text-ink-500">{{ $post->status === 'published' ? __('Published') : __('Draft') }}</span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-ink-500">{{ $post->updated_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-brand-600 hover:underline">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-ink-500">{{ __('No posts match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>
