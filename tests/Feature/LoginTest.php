<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

it('logs an admin in and redirects to the admin dashboard', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);
    $user->assignRole('admin');

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('submit')
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('logs a clinic member in and redirects to their clinic dashboard', function () {
    $country = Country::create([
        'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'slug' => 'turkey',
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'istanbul']);
    $clinic = Clinic::create([
        'slug' => 'test-clinic', 'name' => ['en' => 'Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create(['password' => bcrypt('secret123')]);
    $clinic->users()->attach($user, ['role' => 'owner']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('submit')
        ->assertRedirect(route('clinic.dashboard', $clinic));
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('submit')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});
