<?php

declare(strict_types=1);

use App\Enums\OfferStatus;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Clinic\OfferBuilder;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Treatment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

function seedOfferClinic(?string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'O'.$iso2, 'name' => 'Offerland '.$n, 'slug' => 'offerland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'offer-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'offer-clinic-'.uniqid(), 'name' => ['en' => 'Offer Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $treatment = Treatment::create([
        'slug' => 'ob-implants-'.uniqid(), 'name' => ['en' => 'OB Implants'],
        'currency' => 'EUR', 'status' => 'published',
    ]);
    $clinic->treatments()->attach($treatment->id, [
        'price_min' => 50000, 'price_max' => 90000, 'currency' => 'EUR', 'is_available' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);

    // Always seed permissions so `offers.manage` exists in the DB — only
    // conditionally assign a role, so the "no permission" test exercises
    // a real permission-denied check rather than a missing-permission
    // exception from Spatie.
    (new RolePermissionSeeder)->run();
    if ($spatieRole) {
        $user->assignRole($spatieRole);
    }

    return [$clinic, $user, $treatment];
}

function seedAcceptedLeadFor(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Patient', 'email' => 'patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('lets an authorized clinic member send an offer with a computed price total', function () {
    [$clinic, $owner, $treatment] = seedOfferClinic();
    $lead = seedAcceptedLeadFor($clinic);

    Livewire::actingAs($owner)
        ->test(OfferBuilder::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('title', 'Implant Plan')
        ->set('valid_until', now()->addDays(10)->format('Y-m-d'))
        ->set('selected.'.$treatment->id, true)
        ->set('prices.'.$treatment->id, '650.00')
        ->set('includes_hotel', true)
        ->call('send')
        ->assertSet('sent', true);

    $offer = Offer::first();

    expect($offer)->not->toBeNull();
    expect($offer->title)->toBe('Implant Plan');
    expect($offer->price_total)->toBe(65000);
    expect($offer->currency)->toBe('EUR');
    expect($offer->status)->toBe(OfferStatus::Sent);
    expect($offer->includes_json['hotel'])->toBeTrue();
    expect($offer->breakdown_json[0]['treatment_id'])->toBe($treatment->id);

    expect($lead->fresh()->status->value)->toBe('offer_sent');
    expect($lead->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('requires at least one selected treatment', function () {
    [$clinic, $owner] = seedOfferClinic();
    $lead = seedAcceptedLeadFor($clinic);

    Livewire::actingAs($owner)
        ->test(OfferBuilder::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('title', 'Implant Plan')
        ->set('valid_until', now()->addDays(10)->format('Y-m-d'))
        ->call('send')
        ->assertHasErrors(['selected']);

    expect(Offer::count())->toBe(0);
});

it('blocks a clinic member without offers.manage from reaching the offer builder', function () {
    [$clinic, $staffWithoutPermission] = seedOfferClinic(spatieRole: null);
    $lead = seedAcceptedLeadFor($clinic);

    $this->actingAs($staffWithoutPermission)
        ->get(route('clinic.offers.create', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('blocks building an offer for a lead the clinic has not accepted yet', function () {
    [$clinic, $owner] = seedOfferClinic();
    $lead = Lead::create(['full_name' => 'Patient', 'email' => 'p@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    $this->actingAs($owner)
        ->get(route('clinic.offers.create', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('lets an authorized admin update an offer status from the lead detail page', function () {
    [$clinic, $owner, $treatment] = seedOfferClinic();
    $lead = seedAcceptedLeadFor($clinic);

    Livewire::actingAs($owner)
        ->test(OfferBuilder::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('title', 'Implant Plan')
        ->set('valid_until', now()->addDays(10)->format('Y-m-d'))
        ->set('selected.'.$treatment->id, true)
        ->set('prices.'.$treatment->id, '650.00')
        ->call('send');

    $offer = Offer::first();

    (new RolePermissionSeeder)->run();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateOfferStatus', $offer->id, 'accepted')
        ->assertHasNoErrors();

    expect($offer->fresh()->status)->toBe(OfferStatus::Accepted);
});

it('blocks a user without offers.manage from updating an offer status', function () {
    [$clinic, $owner, $treatment] = seedOfferClinic();
    $lead = seedAcceptedLeadFor($clinic);

    Livewire::actingAs($owner)
        ->test(OfferBuilder::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('title', 'Implant Plan')
        ->set('valid_until', now()->addDays(10)->format('Y-m-d'))
        ->set('selected.'.$treatment->id, true)
        ->set('prices.'.$treatment->id, '650.00')
        ->call('send');

    $offer = Offer::first();

    (new RolePermissionSeeder)->run();
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateOfferStatus', $offer->id, 'accepted')
        ->assertForbidden();

    expect($offer->fresh()->status)->toBe(OfferStatus::Sent);
});
