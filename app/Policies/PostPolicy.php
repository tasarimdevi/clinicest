<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Mirrors ClinicPolicy's update/manage/verify split: `update` gates
 * ordinary field edits, `publish` gates the specific act of taking a post
 * live — a content_editor has content.edit but not content.publish
 * (RolePermissionSeeder), matching the "draft -> review -> approve" gate
 * docs/07-ai-architecture.md §4 requires for any published content.
 */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('content.view');
    }

    public function create(User $user): bool
    {
        return $user->can('content.edit');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('content.edit');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('content.publish');
    }
}
