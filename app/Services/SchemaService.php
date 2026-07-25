<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Clinic;
use App\Models\Post;
use App\Models\Treatment;

/**
 * Builds JSON-LD structured-data graphs per page type.
 * See docs/06-seo-architecture.md §4. One graph per page, rendered via
 * a single <script type="application/ld+json"> in the layout head.
 */
class SchemaService
{
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
        ];
    }

    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            // The last item (current page) legitimately has no 'url' — it's
            // not a link. Google's guidelines don't require `item` on it.
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ], fn ($value) => $value !== null))->all(),
        ];
    }

    public function medicalProcedure(Treatment $treatment): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalProcedure',
            'name' => $treatment->getTranslation('name', app()->getLocale()),
            'description' => $treatment->getTranslation('summary', app()->getLocale()),
        ];
    }

    public function medicalClinic(Clinic $clinic): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalClinic',
            'name' => $clinic->getTranslation('name', app()->getLocale()),
            'url' => route('clinics.show', $clinic->slug),
        ];

        if ($clinic->rating_count > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $clinic->rating_avg,
                'reviewCount' => $clinic->rating_count,
            ];
        }

        return $data;
    }

    /**
     * docs/04-wireframes.md §11-12: blog posts use Article/BlogPosting,
     * guide pillar/cluster pages use Article/MedicalWebPage.
     */
    public function article(Post $post): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $post->kind === 'blog' ? 'BlogPosting' : 'MedicalWebPage',
            'headline' => $post->getTranslation('title', app()->getLocale()),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
        ];

        if ($post->author_name) {
            $data['author'] = ['@type' => 'Person', 'name' => $post->author_name];
        }

        if ($post->medical_reviewer_name) {
            $data['reviewedBy'] = ['@type' => 'Person', 'name' => $post->medical_reviewer_name];
        }

        return $data;
    }

    public function faqPage(iterable $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->getTranslation('question', app()->getLocale()),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq->getTranslation('answer', app()->getLocale()),
                ],
            ])->all(),
        ];
    }
}
