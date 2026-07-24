<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SEO configuration
|--------------------------------------------------------------------------
| See docs/06-seo-architecture.md.
*/

return [

    'default_title_suffix' => env('APP_NAME', 'Clinicest'),

    'organization' => [
        'name' => 'Clinicest',
        'legal_name' => env('SEO_ORG_LEGAL_NAME', 'Clinicest'),
        'logo' => env('SEO_ORG_LOGO', '/images/logo.png'),
    ],

    /*
    | Sitemaps are split by entity type per docs/06-seo-architecture.md §7,
    | so any one file stays small and re-generates fast.
    */
    'sitemaps' => [
        'treatments', 'clinics', 'doctors', 'posts', 'countries', 'cost',
    ],

    /*
    | Programmatic page quality gate — a generated page is not indexed
    | until it clears these minimums. See docs/06-seo-architecture.md §2.
    */
    'quality_gate' => [
        'min_words' => 300,
        'min_related_entities' => 1,
    ],

];
