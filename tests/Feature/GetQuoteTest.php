<?php

declare(strict_types=1);

use App\Livewire\Public\GetQuote;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Treatment;
use Livewire\Livewire;

it('creates a lead when the form is submitted with valid data and consent', function () {
    $treatment = Treatment::create([
        'slug' => 'dental-implants',
        'name' => ['en' => 'Dental Implants'],
        'currency' => 'EUR',
        'status' => 'published',
    ]);

    $country = Country::create([
        'iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'slug' => 'uk',
        'currency' => 'GBP', 'is_target' => true, 'tier' => 'primary',
    ]);

    Livewire::test(GetQuote::class)
        ->set('primary_treatment_id', $treatment->id)
        ->set('country_id', $country->id)
        ->set('full_name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('whatsapp', '+447700900000')
        ->set('message', 'Interested in implants')
        ->set('consent', true)
        ->call('submit')
        ->assertSet('submitted', true);

    $lead = Lead::first();

    expect($lead)->not->toBeNull();
    expect($lead->full_name)->toBe('Jane Doe');
    expect($lead->email)->toBe('jane@example.com');
    expect($lead->status->value)->toBe('new');
    expect($lead->primary_treatment_id)->toBe($treatment->id);
    expect($lead->country_id)->toBe($country->id);
    expect($lead->consents()->where('granted', true)->exists())->toBeTrue();
    expect($lead->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('requires consent before creating a lead', function () {
    Livewire::test(GetQuote::class)
        ->set('full_name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('consent', false)
        ->call('submit')
        ->assertHasErrors(['consent']);

    expect(Lead::count())->toBe(0);
});

it('requires a valid email', function () {
    Livewire::test(GetQuote::class)
        ->set('full_name', 'Jane Doe')
        ->set('email', 'not-an-email')
        ->set('consent', true)
        ->call('submit')
        ->assertHasErrors(['email']);
});

/*
 * These pre-fill tests hit the real route rather than Livewire::test():
 * mount() reads request()->integer()/->query(), and Livewire's component
 * test harness doesn't route a mutated/rebound Request through to that
 * call the way an actual HTTP request does.
 */
it('pre-fills the treatment from a ?treatment= query parameter', function () {
    $treatment = Treatment::create([
        'slug' => 'veneers', 'name' => ['en' => 'Veneers'], 'currency' => 'EUR', 'status' => 'published',
    ]);

    $this->get(route('get-quote', ['treatment' => $treatment->id]))
        ->assertOk()
        ->assertSee('selected', false);
});

it('ignores a ?treatment= query parameter pointing at an unpublished treatment', function () {
    $treatment = Treatment::create([
        'slug' => 'draft-treatment', 'name' => ['en' => 'Draft'], 'currency' => 'EUR', 'status' => 'draft',
    ]);

    $this->get(route('get-quote', ['treatment' => $treatment->id]))
        ->assertOk()
        ->assertDontSee('selected', false);
});

it('pre-fills the message from a ?clinic= query parameter', function () {
    $country = Country::create([
        'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'slug' => 'gq-turkey',
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'gq-istanbul']);
    $clinic = Clinic::create([
        'slug' => 'gq-clinic', 'name' => ['en' => 'Istanbul Smile Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $this->get(route('get-quote', ['clinic' => $clinic->id]))
        ->assertOk()
        ->assertSee("I'm interested in a quote from Istanbul Smile Clinic.");
});
