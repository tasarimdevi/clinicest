<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

it('renders the admin dashboard in english by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Open Leads')
        ->assertDontSee('Açık Lead');
});

it('lets an authenticated user switch the admin panel to turkish via session, with no locale URL prefix', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('settings.locale', 'tr'))
        ->assertRedirect();

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Açık Lead')
        ->assertDontSee('Open Leads');
});

it('rejects an unsupported locale on the switch route', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('settings.locale', 'de'))->assertNotFound();
});

it('guests cannot use the locale switch route', function () {
    $this->get(route('settings.locale', 'tr'))->assertRedirect(route('login'));
});

it('renders the admin dashboard without a leaked dotted key once switched to turkish', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('settings.locale', 'tr'));

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('nav.contact');
});
