<?php

declare(strict_types=1);

use App\Enums\VerificationTier;
use App\Livewire\Admin\ClinicForm;
use App\Livewire\Admin\Clinics;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'A'.$iso2, 'name' => 'Testland '.$n, 'slug' => 'testland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'istanbul-'.$n]);
}

it('blocks users without clinics.view from the clinics list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access-admin');

    $this->actingAs($user)->get(route('admin.clinics.index'))->assertForbidden();
});

it('lets an admin view and filter the clinics list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = seedCity();
    Clinic::create(['slug' => 'a-clinic', 'name' => ['en' => 'Alpha Dental'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);
    Clinic::create(['slug' => 'b-clinic', 'name' => ['en' => 'Beta Dental'], 'city_id' => $city->id, 'verification_tier' => 'elite', 'is_active' => true]);

    Livewire::actingAs($admin)
        ->test(Clinics::class)
        ->assertSee('Alpha Dental')
        ->assertSee('Beta Dental')
        ->set('tier', 'elite')
        ->assertSee('Beta Dental')
        ->assertDontSee('Alpha Dental');
});

it('lets an admin toggle a clinic active/featured', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = seedCity();
    $clinic = Clinic::create(['slug' => 'a-clinic', 'name' => ['en' => 'Alpha Dental'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => false, 'is_featured' => false]);

    Livewire::actingAs($admin)
        ->test(Clinics::class)
        ->call('toggleActive', $clinic->id)
        ->call('toggleFeatured', $clinic->id);

    expect($clinic->fresh()->is_active)->toBeTrue();
    expect($clinic->fresh()->is_featured)->toBeTrue();
});

it('lets a user with clinics.manage create a new clinic', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = seedCity();

    Livewire::actingAs($admin)
        ->test(ClinicForm::class)
        ->set('name.en', 'New Clinic')
        ->set('name.tr', 'Yeni Klinik')
        ->set('slug', 'new-clinic')
        ->set('city_id', $city->id)
        ->call('save');

    $clinic = Clinic::where('slug', 'new-clinic')->first();
    expect($clinic)->not->toBeNull();
    expect($clinic->getTranslation('name', 'en'))->toBe('New Clinic');
    expect($clinic->getTranslation('name', 'tr'))->toBe('Yeni Klinik');
});

it('blocks a user without clinics.manage from creating a clinic', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator'); // has access-admin + clinics.verify, not clinics.manage

    // A mount()-time authorization failure on a full-page Livewire
    // component doesn't propagate as a raw exception through
    // Livewire::test() the way a later ->call() does — it's rendered as
    // an actual 403 response, so we assert against the real route
    // (same pattern already proven in AdminLeadsTest for Leads::mount()).
    $this->actingAs($moderator)->get(route('admin.clinics.create'))->assertForbidden();
});

it('lets a moderator reach the edit page and change the verification tier', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $city = seedCity();
    $clinic = Clinic::create(['slug' => 'a-clinic', 'name' => ['en' => 'Alpha Dental'], 'city_id' => $city->id, 'verification_tier' => 'pending']);

    Livewire::actingAs($moderator)
        ->test(ClinicForm::class, ['clinic' => $clinic])
        ->call('updateVerificationTier', 'verified');

    expect($clinic->fresh()->verification_tier)->toBe(VerificationTier::Verified);
});

it('blocks a moderator from editing clinic profile fields, only the tier', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $city = seedCity();
    $clinic = Clinic::create(['slug' => 'a-clinic', 'name' => ['en' => 'Alpha Dental'], 'city_id' => $city->id, 'verification_tier' => 'pending']);

    Livewire::actingAs($moderator)
        ->test(ClinicForm::class, ['clinic' => $clinic])
        ->set('name.en', 'Hacked Name')
        ->call('save')
        ->assertForbidden();

    expect($clinic->fresh()->getTranslation('name', 'en'))->toBe('Alpha Dental');
});

it('blocks a sales agent (no clinics.view) from opening the clinic edit page', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $city = seedCity();
    $clinic = Clinic::create(['slug' => 'a-clinic', 'name' => ['en' => 'Alpha Dental'], 'city_id' => $city->id, 'verification_tier' => 'pending']);

    $this->actingAs($agent)->get(route('admin.clinics.edit', $clinic))->assertForbidden();
});

it('hydrates existing translations into the edit form and edits one locale without wiping the other', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = seedCity();
    $clinic = Clinic::create([
        'slug' => 'bilingual-clinic', 'name' => ['en' => 'English Name', 'tr' => 'Türkçe Ad'],
        'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $component = Livewire::actingAs($admin)->test(ClinicForm::class, ['clinic' => $clinic]);
    $component->assertSet('name.en', 'English Name');
    $component->assertSet('name.tr', 'Türkçe Ad');

    // Change only Turkish; English must survive.
    $component->set('name.tr', 'Güncellenen Ad')->call('save');

    $clinic->refresh();
    expect($clinic->getTranslation('name', 'en'))->toBe('English Name');
    expect($clinic->getTranslation('name', 'tr'))->toBe('Güncellenen Ad');
});

it('does not persist an empty translation so the fallback locale still shows', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = seedCity();

    Livewire::actingAs($admin)
        ->test(ClinicForm::class)
        ->set('name.en', 'Only English')
        ->set('name.tr', '') // left blank
        ->set('slug', 'only-english')
        ->set('city_id', $city->id)
        ->call('save');

    $clinic = Clinic::where('slug', 'only-english')->firstOrFail();
    // tr was blank, so it must not be stored — getTranslation falls back to en.
    expect($clinic->getTranslations('name'))->toBe(['en' => 'Only English']);
    expect($clinic->getTranslation('name', 'tr'))->toBe('Only English');
});
