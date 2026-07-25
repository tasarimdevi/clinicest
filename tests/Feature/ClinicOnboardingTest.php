<?php

declare(strict_types=1);

use App\Livewire\Admin\ClinicApplications;
use App\Livewire\Public\ClinicApplicationPage;
use App\Mail\ClinicApplicationDecisionMail;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedOnboardingCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'B'.$iso2, 'name' => 'Onboardland '.$n, 'slug' => 'onboardland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'onboard-istanbul-'.$n]);
}

it('lets a prospective owner apply and creates a pending clinic + account', function () {
    $city = seedOnboardingCity();

    Livewire::test(ClinicApplicationPage::class)
        ->set('clinic_name', 'New Smile Clinic')
        ->set('city_id', $city->id)
        ->set('owner_name', 'Jane Owner')
        ->set('owner_email', 'jane@example.com')
        ->set('password', 'a-strong-password-1')
        ->set('password_confirmation', 'a-strong-password-1')
        ->call('submit');

    $clinic = Clinic::where('slug', 'new-smile-clinic')->first();
    $user = User::where('email', 'jane@example.com')->first();

    expect($clinic)->not->toBeNull();
    expect($clinic->application_status)->toBe('pending');
    expect($clinic->is_active)->toBeFalse();
    expect($clinic->verification_tier->value)->toBe('pending');
    expect($clinic->owner_user_id)->toBe($user->id);

    expect($user)->not->toBeNull();
    expect($user->hasRole('clinic_owner'))->toBeTrue();

    expect($clinic->users()->where('user_id', $user->id)->wherePivot('role', 'owner')->exists())->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('generates a unique slug when the clinic name collides', function () {
    $city = seedOnboardingCity();
    Clinic::create(['slug' => 'busy-clinic', 'name' => ['en' => 'Busy Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified']);

    Livewire::test(ClinicApplicationPage::class)
        ->set('clinic_name', 'Busy Clinic')
        ->set('city_id', $city->id)
        ->set('owner_name', 'Second Owner')
        ->set('owner_email', 'second@example.com')
        ->set('password', 'a-strong-password-1')
        ->set('password_confirmation', 'a-strong-password-1')
        ->call('submit');

    expect(Clinic::where('slug', 'busy-clinic-2')->exists())->toBeTrue();
});

it('requires a matching password confirmation and a unique owner email', function () {
    $city = seedOnboardingCity();
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(ClinicApplicationPage::class)
        ->set('clinic_name', 'Another Clinic')
        ->set('city_id', $city->id)
        ->set('owner_name', 'Owner')
        ->set('owner_email', 'taken@example.com')
        ->set('password', 'a-strong-password-1')
        ->set('password_confirmation', 'does-not-match')
        ->call('submit')
        ->assertHasErrors(['owner_email', 'password']);

    expect(Clinic::count())->toBe(0);
});

it('lets a moderator (clinics.verify, no clinics.view) approve an application and notifies the owner', function () {
    Mail::fake();

    $city = seedOnboardingCity();
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $clinic = Clinic::create([
        'slug' => 'pending-clinic', 'name' => ['en' => 'Pending Clinic'], 'city_id' => $city->id,
        'owner_user_id' => $owner->id, 'verification_tier' => 'pending', 'is_active' => false,
        'application_status' => 'pending', 'applied_at' => now(),
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator'); // access-admin + clinics.verify, no clinics.view

    Livewire::actingAs($moderator)
        ->test(ClinicApplications::class)
        ->call('approve', $clinic->id);

    $clinic->refresh();
    expect($clinic->application_status)->toBe('approved');
    expect($clinic->is_active)->toBeTrue();
    expect($clinic->verification_tier->value)->toBe('verified');
    expect($clinic->verified_by)->toBe($moderator->id);

    Mail::assertSent(ClinicApplicationDecisionMail::class, fn ($mail) => $mail->hasTo('owner@example.com'));
});

it('lets an admin reject an application with a reason and notifies the owner', function () {
    Mail::fake();

    $city = seedOnboardingCity();
    $owner = User::factory()->create(['email' => 'rejectee@example.com']);
    $clinic = Clinic::create([
        'slug' => 'reject-clinic', 'name' => ['en' => 'Reject Clinic'], 'city_id' => $city->id,
        'owner_user_id' => $owner->id, 'verification_tier' => 'pending', 'is_active' => false,
        'application_status' => 'pending', 'applied_at' => now(),
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(ClinicApplications::class)
        ->set('rejectReason.'.$clinic->id, 'Missing a valid practice license.')
        ->call('reject', $clinic->id);

    $clinic->refresh();
    expect($clinic->application_status)->toBe('rejected');
    expect($clinic->is_active)->toBeFalse();
    expect($clinic->rejection_reason)->toBe('Missing a valid practice license.');

    Mail::assertSent(ClinicApplicationDecisionMail::class, fn ($mail) => $mail->hasTo('rejectee@example.com'));
});

it('blocks a user without clinics.verify from reaching the applications queue', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent'); // access-admin, no clinics.verify

    $this->actingAs($agent)->get(route('admin.clinics.applications'))->assertForbidden();
});

it('only lists pending applications, not already-approved or rejected clinics', function () {
    $city = seedOnboardingCity();
    Clinic::create(['slug' => 'co-pending', 'name' => ['en' => 'Co Pending'], 'city_id' => $city->id, 'application_status' => 'pending', 'applied_at' => now()]);
    Clinic::create(['slug' => 'co-approved', 'name' => ['en' => 'Co Approved'], 'city_id' => $city->id, 'application_status' => 'approved', 'applied_at' => now()]);
    Clinic::create(['slug' => 'co-rejected', 'name' => ['en' => 'Co Rejected'], 'city_id' => $city->id, 'application_status' => 'rejected', 'applied_at' => now()]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(ClinicApplications::class)
        ->assertSee('Co Pending')
        ->assertDontSee('Co Approved')
        ->assertDontSee('Co Rejected');
});

it('shows a pending-review banner on the clinic dashboard for an unapproved application', function () {
    $city = seedOnboardingCity();
    $owner = User::factory()->create();
    $clinic = Clinic::create([
        'slug' => 'banner-clinic', 'name' => ['en' => 'Banner Clinic'], 'city_id' => $city->id,
        'owner_user_id' => $owner->id, 'application_status' => 'pending',
    ]);
    $clinic->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner)
        ->get(route('clinic.dashboard', $clinic))
        ->assertOk()
        ->assertSee('under review');
});
