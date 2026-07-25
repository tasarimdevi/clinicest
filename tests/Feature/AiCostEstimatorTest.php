<?php

declare(strict_types=1);

use App\Livewire\Public\AiCostEstimator;
use App\Livewire\Public\GetQuote;
use App\Models\Country;
use App\Models\CountryTreatment;
use App\Models\Treatment;
use App\Services\CostEstimatorService;
use Livewire\Livewire;

function seedEstimatorTreatment(array $overrides = []): Treatment
{
    return Treatment::create(array_merge([
        'slug' => 'ce-implants', 'name' => ['en' => 'CE Implants'],
        'base_price_min' => 45000, 'base_price_max' => 90000,
        'currency' => 'EUR', 'status' => 'published',
    ], $overrides));
}

function seedEstimatorCountry(array $overrides = []): Country
{
    return Country::create(array_merge([
        'iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'slug' => 'ce-uk',
        'currency' => 'GBP', 'is_target' => true, 'tier' => 'primary',
    ], $overrides));
}

it('estimates from country_treatment data when a pairing exists', function () {
    $treatment = seedEstimatorTreatment();
    $country = seedEstimatorCountry();

    CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    $estimate = app(CostEstimatorService::class)->estimate($treatment, $country);

    expect($estimate['source'])->toBe('country_treatment');
    expect($estimate['local_min'])->toBe(200000);
    expect($estimate['savings_pct'])->toBe(50);
});

it('falls back to the treatment base price when no country_treatment row exists', function () {
    $treatment = seedEstimatorTreatment();
    $country = seedEstimatorCountry(['slug' => 'ce-de', 'iso2' => 'DE', 'iso3' => 'DEU']);

    $estimate = app(CostEstimatorService::class)->estimate($treatment, $country);

    expect($estimate['source'])->toBe('treatment_base');
    expect($estimate['local_min'])->toBeNull();
    expect($estimate['turkey_min'])->toBe(45000);
});

it('falls back to the treatment base price when no country is selected', function () {
    $treatment = seedEstimatorTreatment();

    $estimate = app(CostEstimatorService::class)->estimate($treatment, null);

    expect($estimate['source'])->toBe('treatment_base');
    expect($estimate['local_min'])->toBeNull();
});

it('returns no price data when the treatment has no base price and no country match', function () {
    $treatment = seedEstimatorTreatment(['slug' => 'ce-no-price', 'base_price_min' => null, 'base_price_max' => null]);

    $estimate = app(CostEstimatorService::class)->estimate($treatment, null);

    expect($estimate['source'])->toBeNull();
});

it('renders the ai cost estimator page and shows a price band once treatment and country are picked', function () {
    $treatment = seedEstimatorTreatment();
    $country = seedEstimatorCountry();

    CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    Livewire::test(AiCostEstimator::class)
        ->set('treatment_id', $treatment->id)
        ->set('country_id', $country->id)
        ->assertSee('CE Implants')
        ->assertSee('50%');
});

it('ignores an unpublished treatment id passed via query string', function () {
    $treatment = seedEstimatorTreatment(['slug' => 'ce-draft', 'status' => 'draft']);

    Livewire::test(AiCostEstimator::class, ['treatment_id' => $treatment->id])
        ->assertSet('treatment_id', null);
});

it('shows the ai-assisted estimate on the get-quote confirmation screen', function () {
    $treatment = seedEstimatorTreatment();
    $country = seedEstimatorCountry();

    CountryTreatment::create([
        'country_id' => $country->id, 'treatment_id' => $treatment->id, 'currency' => 'GBP',
        'local_price_min' => 200000, 'local_price_max' => 300000,
        'turkey_price_min' => 100000, 'turkey_price_max' => 150000,
    ]);

    Livewire::test(GetQuote::class)
        ->set('primary_treatment_id', $treatment->id)
        ->set('country_id', $country->id)
        ->set('full_name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('consent', true)
        ->call('submit')
        ->assertSee('AI-assisted estimate')
        ->assertSee('1,000')
        ->assertSee('1,500');
});
