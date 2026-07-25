<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Treatment;
use App\Models\User;

/**
 * Treatments are site content, gated on the same content.* abilities as
 * posts (see PostPolicy) — content_editor edits, content.publish is the
 * separate act of taking a treatment live (its status draft -> published).
 */
class TreatmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view');
    }

    public function view(User $user, Treatment $treatment): bool
    {
        return $user->can('content.view');
    }

    public function create(User $user): bool
    {
        return $user->can('content.edit');
    }

    public function update(User $user, Treatment $treatment): bool
    {
        return $user->can('content.edit');
    }

    public function publish(User $user, Treatment $treatment): bool
    {
        return $user->can('content.publish');
    }
}
