<?php

declare(strict_types=1);

use App\Livewire\Public\BeforeAfterIndex;
use App\Models\BeforeAfterCase;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Treatment;
use Livewire\Livewire;

function seedClinicForBeforeAfter(): Clinic
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'B'.$iso2, 'name' => 'BA-Land '.$n, 'slug' => 'ba-land-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'ba-istanbul-'.$n]);

    return Clinic::create([
        'slug' => 'ba-clinic-'.$n, 'name' => ['en' => 'BA Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

it('shows only published before/after cases on the hub', function () {
    $clinic = seedClinicForBeforeAfter();
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    BeforeAfterCase::create([
        'clinic_id' => $clinic->id, 'treatment_id' => $treatment->id,
        'title' => ['en' => 'Published Case'], 'is_published' => true,
    ]);
    BeforeAfterCase::create([
        'clinic_id' => $clinic->id, 'treatment_id' => $treatment->id,
        'title' => ['en' => 'Hidden Case'], 'is_published' => false,
    ]);

    Livewire::test(BeforeAfterIndex::class)
        ->assertSee('Published Case')
        ->assertDontSee('Hidden Case');
});

it('filters the before/after hub by treatment', function () {
    $clinic = seedClinicForBeforeAfter();
    $implants = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);
    $veneers = Treatment::create(['slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'status' => 'published']);

    BeforeAfterCase::create(['clinic_id' => $clinic->id, 'treatment_id' => $implants->id, 'title' => ['en' => 'Implant Case'], 'is_published' => true]);
    BeforeAfterCase::create(['clinic_id' => $clinic->id, 'treatment_id' => $veneers->id, 'title' => ['en' => 'Veneer Case'], 'is_published' => true]);

    Livewire::test(BeforeAfterIndex::class)
        ->set('treatment', (string) $implants->id)
        ->assertSee('Implant Case')
        ->assertDontSee('Veneer Case');
});

it('shows an honest pending state for a case with no photos yet', function () {
    $clinic = seedClinicForBeforeAfter();
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    BeforeAfterCase::create([
        'clinic_id' => $clinic->id, 'treatment_id' => $treatment->id,
        'title' => ['en' => 'No Photos Yet'], 'is_published' => true,
    ]);

    Livewire::test(BeforeAfterIndex::class)->assertSee('Photos pending');
});
