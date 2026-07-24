<?php

declare(strict_types=1);

use App\Livewire\Public\ClinicsIndex;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Treatment;
use Livewire\Livewire;

function seedPublicCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'A'.$iso2, 'name' => 'Testland '.$n, 'slug' => 'pub-testland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'pub-istanbul-'.$n]);
}

it('renders the clinics directory with active clinics only', function () {
    $city = seedPublicCity();
    Clinic::create(['slug' => 'active-clinic', 'name' => ['en' => 'Active Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);
    Clinic::create(['slug' => 'inactive-clinic', 'name' => ['en' => 'Inactive Clinic'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => false]);

    Livewire::test(ClinicsIndex::class)
        ->assertSee('Active Clinic')
        ->assertDontSee('Inactive Clinic');
});

it('filters the clinics directory by treatment', function () {
    $city = seedPublicCity();
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    $withTreatment = Clinic::create(['slug' => 'with-treatment', 'name' => ['en' => 'With Treatment'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);
    Clinic::create(['slug' => 'without-treatment', 'name' => ['en' => 'Without Treatment'], 'city_id' => $city->id, 'verification_tier' => 'verified', 'is_active' => true]);

    $withTreatment->treatments()->attach($treatment->id, ['currency' => 'EUR', 'is_available' => true]);

    Livewire::test(ClinicsIndex::class)
        ->set('treatment', (string) $treatment->id)
        ->assertSee('With Treatment')
        ->assertDontSee('Without Treatment');
});

it('renders an active clinic profile with treatments and doctors', function () {
    $city = seedPublicCity();
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    $clinic = Clinic::create([
        'slug' => 'test-clinic', 'name' => ['en' => 'Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'elite', 'is_active' => true,
    ]);
    $clinic->treatments()->attach($treatment->id, ['price_min' => 45000, 'price_max' => 90000, 'currency' => 'EUR', 'is_available' => true]);
    Doctor::create(['slug' => 'dr-test', 'clinic_id' => $clinic->id, 'full_name' => 'Dr. Test Person']);

    $this->get(route('clinics.show', $clinic->slug))
        ->assertOk()
        ->assertSee('Test Clinic')
        ->assertSee('Implants')
        ->assertSee('Dr. Test Person');
});

it('404s for an inactive clinic profile', function () {
    $city = seedPublicCity();
    $clinic = Clinic::create(['slug' => 'inactive', 'name' => ['en' => 'Inactive'], 'city_id' => $city->id, 'verification_tier' => 'pending', 'is_active' => false]);

    $this->get(route('clinics.show', $clinic->slug))->assertNotFound();
});
