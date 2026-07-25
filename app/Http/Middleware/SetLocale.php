<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale from the required /{locale}/ path prefix
 * (docs/06-seo-architecture.md §5). The URL is the source of truth: a
 * shared /tr/... link always renders Turkish regardless of any prior
 * session choice. Session/Accept-Language only seed the locale for entry
 * points without a prefix (e.g. the `/` redirect). URL::defaults then makes
 * every route('...') call — including positional route('x.show', $model)
 * ones — generate the current locale's prefixed URL with no call-site
 * changes (see routes/web/public.php).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('clinicest.locales.supported', ['en']);
        $default = config('clinicest.locales.default', 'en');

        $segment = $request->segment(1);
        $locale = null;

        if ($segment !== null && in_array($segment, $supported, true)) {
            $locale = $segment;
        } elseif ($request->session()->has('locale') && in_array($request->session()->get('locale'), $supported, true)) {
            $locale = $request->session()->get('locale');
        } else {
            $locale = $request->getPreferredLanguage($supported) ?? $default;
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
