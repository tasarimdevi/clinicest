<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

it('seeds the documented role matrix', function () {
    (new RolePermissionSeeder)->run();

    expect(Role::pluck('name')->all())->toEqualCanonicalizing([
        'patient', 'clinic_owner', 'clinic_manager', 'clinic_staff', 'doctor',
        'sales_agent', 'content_editor', 'seo_manager', 'moderator', 'finance',
        'admin', 'super_admin',
    ]);
});

it('grants super_admin every permission', function () {
    (new RolePermissionSeeder)->run();

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($user->can('access-admin'))->toBeTrue();
    expect($user->can('leads.assign'))->toBeTrue();
    expect($user->can('roles.manage'))->toBeTrue();
});

it('scopes clinic_staff to lead visibility only', function () {
    (new RolePermissionSeeder)->run();

    $user = User::factory()->create();
    $user->assignRole('clinic_staff');

    expect($user->can('leads.view'))->toBeTrue();
    expect($user->can('clinics.manage'))->toBeFalse();
    expect($user->can('access-admin'))->toBeFalse();
});
