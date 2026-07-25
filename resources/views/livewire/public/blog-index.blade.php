<div>
    <div class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('Blog')],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Blog') }}</h1>

        @if ($categories->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                <button wire:click="$set('category', '')"
                        @class(['rounded-full px-3 py-1.5 text-xs font-medium', 'bg-brand-700 text-white' => $category === '', 'bg-ink-100 text-ink-600 hover:bg-ink-200' => $category !== ''])>
                    {{ __('All') }}
                </button>
                @foreach ($categories as $c)
                    <button wire:click="$set('category', '{{ $c->id }}')"
                            @class(['rounded-full px-3 py-1.5 text-xs font-medium', 'bg-brand-700 text-white' => (string) $category === (string) $c->id, 'bg-ink-100 text-ink-600 hover:bg-ink-200' => (string) $category !== (string) $c->id])>
                        {{ $c->getTranslation('name', app()->getLocale()) }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if ($featured)
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <a href="{{ route('blog.show', $featured->slug) }}" class="grid grid-cols-1 gap-6 rounded-2xl border border-ink-200 bg-white p-6 shadow-card sm:grid-cols-2 sm:p-8">
                <div>
                    <p class="font-mono text-xs font-semibold uppercase tracking-wide text-gold-600">{{ __('Latest') }}</p>
                    <h2 class="mt-2 font-serif text-2xl font-medium text-ink-900">{{ $featured->getTranslation('title', app()->getLocale()) }}</h2>
                    @if ($excerpt = $featured->getTranslation('excerpt', app()->getLocale()))
                        <p class="mt-3 text-sm text-ink-600">{{ $excerpt }}</p>
                    @endif
                    <p class="mt-4 text-xs text-ink-500">
                        {{ $featured->published_at?->format('d M Y') }} &middot; {{ __(':minutes min read', ['minutes' => $featured->readingMinutes()]) }}
                    </p>
                </div>
                @if ($featured->hero_image_path)
                    <img src="{{ $featured->hero_image_path }}" alt="" class="aspect-video w-full rounded-lg object-cover">
                @endif
            </a>
        </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" wire:key="post-{{ $post->id }}" class="block rounded-lg border border-ink-200 bg-white p-5 shadow-card hover:border-brand-300">
                    @if ($post->category)
                        <p class="font-mono text-xs font-semibold uppercase tracking-wide text-gold-600">
                            {{ $post->category->getTranslation('name', app()->getLocale()) }}
                        </p>
                    @endif
                    <h3 class="mt-2 font-serif text-lg font-medium text-ink-900">{{ $post->getTranslation('title', app()->getLocale()) }}</h3>
                    @if ($excerpt = $post->getTranslation('excerpt', app()->getLocale()))
                        <p class="mt-2 text-sm text-ink-600">{{ \Illuminate\Support\Str::limit($excerpt, 110) }}</p>
                    @endif
                    <p class="mt-3 text-xs text-ink-500">{{ $post->published_at?->format('d M Y') }}</p>
                </a>
            @empty
                <p class="col-span-full text-sm text-ink-500">{{ __('No posts published yet — check back soon.') }}</p>
            @endforelse
        </div>
    </div>
</div>
