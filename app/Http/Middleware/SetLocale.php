<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale: path prefix (/tr/...) -> session -> browser
 * Accept-Language -> config default. See docs/08-laravel-architecture.md §3.
 * English is served at the root with no prefix (canonical + x-default);
 * additional locales live under /{locale}/...
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('clinicest.locales.supported', ['en']);
        $default = config('clinicest.locales.default', 'en');

        $segment = $request->segment(1);
        $locale = null;

        if ($segment !== null && in_array($segment, $supported, true) && $segment !== $default) {
            $locale = $segment;
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } else {
            $preferred = $request->getPreferredLanguage($supported);
            $locale = $preferred ?? $default;
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
