<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Documents can be genuinely sensitive (x-rays, treatment plans), so
 * `view`/`delete` aren't just permission checks like most policies here —
 * they also confirm the user actually belongs to the document's own
 * clinic (mirrors EnsureClinicMember's own check), so one clinic can
 * never read or remove another clinic's uploads. Admin/access-admin
 * staff with documents.view bypass the clinic-membership check, since
 * they're not scoped to a single clinic to begin with.
 */
class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view') && $this->belongsToUsersScope($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->can('documents.manage');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.manage') && $this->belongsToUsersScope($user, $document);
    }

    protected function belongsToUsersScope(User $user, Document $document): bool
    {
        if ($user->can('access-admin')) {
            return true;
        }

        return $user->clinics()->whereKey($document->clinic_id)->exists();
    }
}
