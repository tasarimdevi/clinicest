<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BeforeAfterCase;
use App\Models\User;

/**
 * Moderation reuses reviews.moderate — approving a before/after case is
 * the same kind of public-trust-content decision as approving a review
 * (authenticity + consent), and the moderator role already holds it.
 * Clinic-side creation/deletion is authorized separately via ClinicPolicy
 * (clinics.manage + clinic membership), not here.
 */
class BeforeAfterCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reviews.moderate');
    }

    public function moderate(User $user, BeforeAfterCase $case): bool
    {
        return $user->can('reviews.moderate');
    }
}
