<?php

declare(strict_types=1);

use App\Livewire\Public\CostShow;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\CountryTreatment;
use App\Models\Faq;
use App\Models\Treatment;
use Livewire\Livewire;

function seedTargetCountry(array $overrides = []): Country
{
    return Country::create(array_merge([
        'iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'slug' => 'cc-uk',
        'currency' => 'GBP', 'is_target' => true, 'tier' => 'primary',
        'primary_language' => 'en',
        'flight_note' => 'Direct flights from London.',
        'avg_flight_hours' => 4.0,
        'visa_info' => 'Visa-free for up to 90 days.',
        'best_time_to_visit' => 'April to June.',
    ], $overrides));
}

function seedPublishedTreatmentForCost(array $overrides = []): Treatment
{
    return Treatment::create(array_merge([
        'slug' => 'cc-implants', 'name' => ['en' => 'CC Implants'],
        'base_price_min' => 45000, 'base_price_max' => 90000,
        'currency' => 'EUR', 'status' => 'published',
    ], $overrides));
}

it('computes the savings percentage on a country_treatment row', function () {
    $country = seedTargetCountry();
    $treatment = seedPublishedTreatmentForCost();

    $ct = CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    expect($ct->savingsPct())->toBe(50);
});

it('renders the country landing page with cost comparison and travel info', function () {
    $country = seedTargetCountry();
    $treatment = seedPublishedTreatmentForCost();

    CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    $this->get(route('countries.show', $country->slug))
        ->assertOk()
        ->assertSee('United Kingdom')
        ->assertSee('CC Implants')
        ->assertSee('Direct flights from London.')
        ->assertSee('50%');
});

it('shows clinics whose languages match the country primary language', function () {
    $country = seedTargetCountry();
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'cc-istanbul']);

    $matching = Clinic::create([
        'slug' => 'cc-en-clinic', 'name' => ['en' => 'English Speaking Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true, 'languages_json' => ['en', 'tr'],
    ]);
    $nonMatching = Clinic::create([
        'slug' => 'cc-de-clinic', 'name' => ['en' => 'German Only Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true, 'languages_json' => ['de'],
    ]);

    $this->get(route('countries.show', $country->slug))
        ->assertOk()
        ->assertSee('English Speaking Clinic')
        ->assertDontSee('German Only Clinic');
});

it('404s for a country that is not a target market', function () {
    $country = Country::create([
        'iso2' => 'XX', 'iso3' => 'XXX', 'name' => 'Nowhere', 'slug' => 'cc-nowhere',
        'currency' => 'USD', 'is_target' => false,
    ]);

    $this->get(route('countries.show', $country->slug))->assertNotFound();
});

it('renders the cost comparison page with a savings calculator', function () {
    $treatment = seedPublishedTreatmentForCost();
    $country = seedTargetCountry();

    CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);
    Faq::create([
        'faqable_type' => Treatment::class, 'faqable_id' => $treatment->id,
        'question' => ['en' => 'Is it safe?'], 'answer' => ['en' => 'Yes.'], 'sort' => 1, 'status' => 'published',
    ]);

    Livewire::test(CostShow::class, ['treatment' => $treatment])
        ->assertSee('CC Implants')
        ->assertSee('United Kingdom')
        ->assertSee('50%')
        ->assertSee('Is it safe?');
});

it('recomputes the calculator when a different country is selected', function () {
    $treatment = seedPublishedTreatmentForCost();
    $uk = seedTargetCountry(['iso2' => 'GB', 'iso3' => 'GBR', 'slug' => 'cc-uk-2', 'name' => 'United Kingdom Two']);
    $de = seedTargetCountry(['iso2' => 'DE', 'iso3' => 'DEU', 'slug' => 'cc-de-2', 'name' => 'Germany Two', 'currency' => 'EUR', 'primary_language' => 'de']);

    CountryTreatment::create([
        'country_id' => $uk->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);
    CountryTreatment::create([
        'country_id' => $de->id, 'treatment_id' => $treatment->id, 'currency' => 'EUR',
        'local_price_min' => 400000, 'local_price_max' => 500000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    Livewire::test(CostShow::class, ['treatment' => $treatment])
        ->assertSee('50%')
        ->set('selected_country_id', $de->id)
        ->assertSee('75%');
});

it('404s for a cost page on a draft treatment', function () {
    $treatment = seedPublishedTreatmentForCost(['slug' => 'cc-draft', 'status' => 'draft']);

    $this->get(route('cost.show', $treatment->slug))->assertNotFound();
});

it('pre-fills the get-quote form country from a query parameter', function () {
    $country = seedTargetCountry();

    $this->get(route('get-quote', ['country' => $country->id]))
        ->assertOk()
        ->assertSee('value="'.$country->id.'" selected', false);
});
