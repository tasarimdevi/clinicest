<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Resolves per-page SEO metadata (title/description/canonical/hreflang).
 * See docs/06-seo-architecture.md §5. Reads seo_meta rows when present,
 * falls back to sensible generated defaults otherwise.
 */
class SeoService
{
    /**
     * @return array<string, string> locale => absolute URL
     */
    public function hreflangAlternates(string $routeName, array $parameters, array $availableLocales): array
    {
        $alternates = [];

        foreach ($availableLocales as $locale) {
            $alternates[$locale] = route($routeName, [...$parameters, 'locale' => $locale]);
        }

        // x-default points at the primary English (root) variant.
        $alternates['x-default'] = $alternates['en'] ?? reset($alternates);

        return $alternates;
    }

    public function defaultTitle(string $pageTitle): string
    {
        return "{$pageTitle} | ".config('app.name');
    }
}
