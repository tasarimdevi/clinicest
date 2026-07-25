<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

/**
 * messages.manage is only granted to clinic roles (owner/manager/staff —
 * docs/09-crm-admin-architecture.md §1 explicitly gives clinic_staff
 * message-reply rights, unlike offers/appointments). sales_agent/admin
 * get messages.view only: LeadDetail shows every clinic's thread for CRM
 * oversight, but composing happens from the clinic side — there's no
 * admin-side composer (would need a clinic picker; not built this pass).
 */
class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('messages.view');
    }

    public function view(User $user, Message $message): bool
    {
        return $user->can('messages.view');
    }

    public function create(User $user): bool
    {
        return $user->can('messages.manage');
    }
}
