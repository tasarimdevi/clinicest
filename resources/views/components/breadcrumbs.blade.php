@props(['items'])

{{--
    $items: [['name' => 'Home', 'url' => route('home')], ..., ['name' => 'Current page']]
    (the last item has no 'url' — it's the current page, not a link).
    Emits both the visual trail and its BreadcrumbList JSON-LD in one place
    so every page that includes this gets correct schema for free.
    See docs/06-seo-architecture.md §4.
--}}
<nav aria-label="{{ __('Breadcrumb') }}" class="mb-6 text-sm text-ink-500">
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach ($items as $i => $item)
            <li class="flex items-center gap-1.5">
                @if ($i > 0)
                    <span aria-hidden="true">/</span>
                @endif
                @if (! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-brand-600">{{ $item['name'] }}</a>
                @else
                    <span class="text-ink-700" aria-current="page">{{ $item['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->breadcrumbs($items), JSON_UNESCAPED_SLASHES) !!}</script>
