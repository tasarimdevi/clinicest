<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

/**
 * Gated on billing.view/commissions.manage rather than leads.*, so
 * finance (billing.view, commissions.manage, no leads.manage) can review
 * and move a commission through pending -> invoiced -> paid without
 * needing rights over the lead or treatment case itself.
 */
class CommissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('billing.view');
    }

    public function view(User $user, Commission $commission): bool
    {
        return $user->can('billing.view');
    }

    public function update(User $user, Commission $commission): bool
    {
        return $user->can('commissions.manage');
    }
}
