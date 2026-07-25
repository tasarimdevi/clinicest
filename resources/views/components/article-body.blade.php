@props(['post'])

{{--
    Shared article template for both Guide (pillar/cluster) and Blog posts
    — see docs/04-wireframes.md §11-12, which specs both as the same
    "article template" shape. The medically-reviewed-by line only renders
    when a real reviewer was recorded (never fabricated); the hero image
    block is skipped entirely when none is set, rather than showing a
    placeholder.
--}}
<div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-500">
    @if ($post->author_name)
        <span>{{ __('By') }} <span class="font-medium text-ink-700">{{ $post->author_name }}</span>@if ($post->author_credential), {{ $post->author_credential }}@endif</span>
    @endif
    @if ($post->medical_reviewer_name)
        <span>{{ __('Medically reviewed by') }} <span class="font-medium text-ink-700">{{ $post->medical_reviewer_name }}</span>@if ($post->medical_reviewer_credential), {{ $post->medical_reviewer_credential }}@endif</span>
    @endif
    @if ($post->published_at)
        <span>{{ $post->published_at->format('d M Y') }}</span>
    @endif
    <span>{{ __(':minutes min read', ['minutes' => $post->readingMinutes()]) }}</span>
</div>

@if ($post->hero_image_path)
    <img src="{{ $post->hero_image_path }}" alt="" class="mt-6 aspect-video w-full rounded-lg object-cover">
@endif

<div class="mt-8 max-w-[66ch] text-sm leading-relaxed text-ink-700 [&_a]:text-brand-700 [&_a]:underline [&_h2]:mt-8 [&_h2]:font-serif [&_h2]:text-xl [&_h2]:font-medium [&_h2]:text-ink-900 [&_h3]:mt-6 [&_h3]:font-serif [&_h3]:text-lg [&_h3]:font-medium [&_h3]:text-ink-900 [&_li]:mt-1 [&_p]:mt-4 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5">
    {!! $post->getTranslation('body', app()->getLocale()) !!}
</div>

@if ($post->treatment)
    <div class="mt-8 rounded-lg border border-ink-200 bg-ink-50 p-4 text-sm">
        {{ __('Related treatment:') }}
        <a href="{{ route('treatments.show', $post->treatment->slug) }}" class="font-medium text-brand-700 hover:underline">
            {{ $post->treatment->getTranslation('name', app()->getLocale()) }}
        </a>
    </div>
@endif

@php $faqs = $post->faqs()->where('status', 'published')->orderBy('sort')->get(); @endphp
@if ($faqs->isNotEmpty())
    <div class="mt-10">
        <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Frequently asked questions') }}</h2>
        <div class="mt-4">
            @foreach ($faqs as $faq)
                <details class="border-b border-ink-200 py-4">
                    <summary class="cursor-pointer font-medium text-ink-900">
                        {{ $faq->getTranslation('question', app()->getLocale()) }}
                    </summary>
                    <p class="mt-2 text-sm text-ink-600">{{ $faq->getTranslation('answer', app()->getLocale()) }}</p>
                </details>
            @endforeach
        </div>
    </div>
    <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->faqPage($faqs), JSON_UNESCAPED_SLASHES) !!}</script>
@endif

<script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->article($post), JSON_UNESCAPED_SLASHES) !!}</script>
