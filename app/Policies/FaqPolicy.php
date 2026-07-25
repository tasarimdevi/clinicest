<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

/**
 * FAQs are site content, gated on the content.* abilities (see
 * PostPolicy). publish() covers taking a FAQ live (status draft ->
 * published), distinct from ordinary edits.
 */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->can('content.view');
    }

    public function create(User $user): bool
    {
        return $user->can('content.edit');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('content.edit');
    }

    public function publish(User $user, Faq $faq): bool
    {
        return $user->can('content.publish');
    }
}
