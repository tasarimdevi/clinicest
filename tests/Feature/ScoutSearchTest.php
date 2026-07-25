<?php

declare(strict_types=1);

use App\Livewire\Public\ClinicsIndex;
use App\Livewire\Public\DoctorsIndex;
use App\Livewire\Public\TreatmentsIndex;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Treatment;
use Livewire\Livewire;

/**
 * Runs against Scout's `collection` driver (forced in phpunit.xml) — real
 * matching logic, zero external dependency. Meilisearch itself (typo
 * tolerance, ranking) is only provable against a live server; that's been
 * smoke-tested manually (see the commit message), not re-asserted here.
 */
function seedScoutCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'S'.$iso2, 'name' => 'Scoutland '.$n, 'slug' => 'scoutland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'scout-istanbul-'.$n]);
}

it('finds a clinic by a partial name match through the scout-backed search', function () {
    $city = seedScoutCity();
    Clinic::create(['slug' => 'bright-smile', 'name' => ['en' => 'Bright Smile Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);
    Clinic::create(['slug' => 'other-clinic', 'name' => ['en' => 'Something Else'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);

    Livewire::test(ClinicsIndex::class)
        ->set('search', 'Bright')
        ->assertSee('Bright Smile Clinic')
        ->assertDontSee('Something Else');
});

it('excludes an inactive clinic from scout-backed clinic search', function () {
    $city = seedScoutCity();
    Clinic::create(['slug' => 'inactive-search', 'name' => ['en' => 'Hidden Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => false]);

    Livewire::test(ClinicsIndex::class)
        ->set('search', 'Hidden')
        ->assertDontSee('Hidden Clinic');
});

it('finds a doctor by a partial name match through the scout-backed search', function () {
    $city = seedScoutCity();
    $clinic = Clinic::create(['slug' => 'doc-clinic', 'name' => ['en' => 'Doc Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);

    Doctor::create(['slug' => 'dr-kaya', 'clinic_id' => $clinic->id, 'full_name' => 'Dr. Elif Kaya']);
    Doctor::create(['slug' => 'dr-other', 'clinic_id' => $clinic->id, 'full_name' => 'Dr. Someone Else']);

    Livewire::test(DoctorsIndex::class)
        ->set('search', 'Kaya')
        ->assertSee('Dr. Elif Kaya')
        ->assertDontSee('Dr. Someone Else');
});

it('excludes a doctor at an inactive clinic from scout-backed doctor search', function () {
    $city = seedScoutCity();
    $inactiveClinic = Clinic::create(['slug' => 'inactive-doc-clinic', 'name' => ['en' => 'Inactive'], 'city_id' => $city->id, 'verification_tier' => 'pending', 'is_active' => false]);

    Doctor::create(['slug' => 'dr-hidden', 'clinic_id' => $inactiveClinic->id, 'full_name' => 'Dr. Hidden Person']);

    Livewire::test(DoctorsIndex::class)
        ->set('search', 'Hidden')
        ->assertDontSee('Dr. Hidden Person');
});

it('re-syncs a doctor search document when its clinic is deactivated', function () {
    $city = seedScoutCity();
    $clinic = Clinic::create(['slug' => 'toggle-clinic', 'name' => ['en' => 'Toggle Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);
    $doctor = Doctor::create(['slug' => 'dr-toggle', 'clinic_id' => $clinic->id, 'full_name' => 'Dr. Toggle Person']);

    expect($doctor->shouldBeSearchable())->toBeTrue();

    $clinic->update(['is_active' => false]);

    expect($doctor->fresh()->shouldBeSearchable())->toBeFalse();
});

it('finds a treatment by a partial name match through the scout-backed search', function () {
    Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Dental Implants'], 'status' => 'published']);
    Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Porcelain Veneers'], 'status' => 'published']);

    Livewire::test(TreatmentsIndex::class)
        ->set('search', 'Implants')
        ->assertSee('Dental Implants')
        ->assertDontSee('Porcelain Veneers');
});

it('excludes a draft treatment from scout-backed treatment search', function () {
    Treatment::create(['slug' => 'draft-implants', 'name' => ['en' => 'Draft Implants'], 'status' => 'draft']);

    Livewire::test(TreatmentsIndex::class)
        ->set('search', 'Draft')
        ->assertDontSee('Draft Implants');
});
