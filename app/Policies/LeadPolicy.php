<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * See docs/09-crm-admin-architecture.md §1 — leads.* permissions are the
 * CRM's gate: view is broad (agents/admin), assign/manage narrower.
 */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('leads.view');
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->can('leads.assign');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('leads.manage');
    }
}
