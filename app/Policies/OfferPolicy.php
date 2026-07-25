<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

/**
 * Offers carry pricing sent to a patient, so — unlike LeadInbox's
 * accept/decline (which relies only on clinic-membership route middleware)
 * — creation and status changes go through policy checks here. Clinic
 * membership for `create()` is still enforced by the `clinic.member`
 * route middleware; this policy only gates the offers.* permission on
 * top of that.
 */
class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('offers.view');
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->can('offers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('offers.manage');
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->can('offers.manage');
    }
}
