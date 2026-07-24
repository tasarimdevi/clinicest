<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Clinic;
use App\Models\User;

/**
 * See docs/09-crm-admin-architecture.md §1 — clinics.verify is deliberately
 * separate from clinics.manage so a moderator can approve/verify a clinic's
 * tier without holding full edit rights over its profile.
 */
class ClinicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clinics.view');
    }

    public function view(User $user, Clinic $clinic): bool
    {
        return $user->can('clinics.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clinics.manage');
    }

    public function update(User $user, Clinic $clinic): bool
    {
        // Broad "can reach the edit page" gate — a moderator with only
        // clinics.verify still needs to land here to use the verification
        // panel. Actually editing the profile fields is gated separately
        // by manage() below, which the form's save() action checks.
        return $user->can('clinics.manage') || $user->can('clinics.verify');
    }

    public function manage(User $user, Clinic $clinic): bool
    {
        return $user->can('clinics.manage');
    }

    public function verify(User $user, Clinic $clinic): bool
    {
        return $user->can('clinics.verify');
    }
}
