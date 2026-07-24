<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scopes clinic-portal routes to users who belong to the clinic in the route
 * (route parameter `clinic`). See docs/09-crm-admin-architecture.md §1 —
 * clinic roles (owner/manager/staff) only ever see their own clinic's data.
 */
class EnsureClinicMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $clinic = $request->route('clinic');

        abort_unless($user && $clinic, 403);

        abort_unless(
            $user->clinics()->whereKey($clinic->id)->exists(),
            403,
            'You do not have access to this clinic.'
        );

        return $next($request);
    }
}
