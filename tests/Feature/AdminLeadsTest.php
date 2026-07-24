<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Admin\Leads;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function makeClinic(): Clinic
{
    $country = Country::create([
        'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'slug' => 'turkey',
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'istanbul']);

    return Clinic::create([
        'slug' => 'test-clinic-'.uniqid(), 'name' => ['en' => 'Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

it('blocks users without access-admin from the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

it('blocks users with access-admin but no leads.view from the lead inbox', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access-admin'); // enough for /admin, not enough for /admin/leads

    $this->actingAs($user)->get(route('admin.leads.index'))->assertForbidden();
});

it('lets an admin view and filter the lead inbox', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Lead::create(['full_name' => 'Alice', 'email' => 'alice@example.com', 'status' => LeadStatus::New]);
    Lead::create(['full_name' => 'Bob', 'email' => 'bob@example.com', 'status' => LeadStatus::Won]);

    Livewire::actingAs($admin)
        ->test(Leads::class)
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->set('status', LeadStatus::Won->value)
        ->assertSee('Bob')
        ->assertDontSee('Alice');
});

it('lets an admin assign a lead to clinics and updates lead status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $clinic = makeClinic();
    $lead = Lead::create(['full_name' => 'Alice', 'email' => 'alice@example.com', 'status' => LeadStatus::New]);

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->set('selectedClinicIds', [$clinic->id])
        ->call('assign');

    expect($lead->fresh()->status)->toBe(LeadStatus::Assigned);
    expect($lead->assignments()->where('clinic_id', $clinic->id)->exists())->toBeTrue();
    expect($lead->activities()->where('type', 'assignment')->exists())->toBeTrue();
});

it('lets an admin change lead status directly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $lead = Lead::create(['full_name' => 'Alice', 'email' => 'alice@example.com', 'status' => LeadStatus::New]);

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateStatus', 'lost');

    expect($lead->fresh()->status)->toBe(LeadStatus::Lost);
});
