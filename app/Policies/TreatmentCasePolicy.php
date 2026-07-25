<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TreatmentCase;
use App\Models\User;

/**
 * Treatment cases are created and moved through their lifecycle by CRM
 * staff (sales_agent/admin, leads.manage) — a lead becoming an actual
 * treatment is squarely part of the lead's own lifecycle, unlike offers/
 * appointments which the clinic drives. Commission status is a separate
 * concern gated by CommissionPolicy (commissions.manage), so finance can
 * manage payouts without needing leads.manage.
 */
class TreatmentCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function view(User $user, TreatmentCase $treatmentCase): bool
    {
        return $user->can('leads.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leads.manage');
    }

    public function update(User $user, TreatmentCase $treatmentCase): bool
    {
        return $user->can('leads.manage');
    }
}
