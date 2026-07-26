<?php

declare(strict_types=1);

use App\Livewire\Clinic\ClinicAnalytics;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\TreatmentCase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedAnalyticsClinic(string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'W'.$iso2, 'name' => 'Analand '.$n, 'slug' => 'analand-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'ana-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'ana-clinic-'.uniqid(), 'name' => ['en' => 'Analytics Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true, 'rating_avg' => 4.6, 'rating_count' => 5,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user->id, ['role' => 'owner']);
    $user->assignRole($spatieRole);

    return [$clinic, $user];
}

it('blocks a clinic manager (no clinics.manage) from the analytics page', function () {
    [$clinic, $manager] = seedAnalyticsClinic('clinic_manager');

    $this->actingAs($manager)->get(route('clinic.analytics', $clinic))->assertForbidden();
});

it('computes lead, acceptance and response KPIs scoped to the clinic', function () {
    [$clinic, $owner] = seedAnalyticsClinic();
    $lead = fn () => Lead::create(['full_name' => 'L', 'email' => 'l'.uniqid().'@e.com', 'status' => 'assigned']);

    // 3 assigned: 2 accepted, 1 declined. Acceptance = 2/3 = 67%.
    $l1 = $lead();
    $l1->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now()->subDays(2), 'responded_at' => now()->subDays(2)->addHours(4)]);
    $l2 = $lead();
    $l2->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now()->subDay(), 'responded_at' => now()->subDay()->addHours(2)]);
    $l3 = $lead();
    $l3->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'declined', 'assigned_at' => now()->subDay(), 'responded_at' => now()->subDay()->addHours(6)]);

    Livewire::actingAs($owner)
        ->test(ClinicAnalytics::class, ['clinic' => $clinic])
        ->assertViewHas('kpis', fn ($kpis) => $kpis['leads'] === 3
            && $kpis['acceptanceRate'] === 67.0
            && $kpis['avgResponseHours'] === 4.0); // (4+2+6)/3 = 4h
});

it('reports completed-case revenue within the selected range', function () {
    [$clinic, $owner] = seedAnalyticsClinic();
    $lead = Lead::create(['full_name' => 'R', 'email' => 'r'.uniqid().'@e.com', 'status' => 'won']);
    TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 800000,
        'currency' => 'EUR', 'status' => 'completed', 'completion_date' => now()->subDays(5),
    ]);

    Livewire::actingAs($owner)
        ->test(ClinicAnalytics::class, ['clinic' => $clinic])
        ->assertViewHas('kpis', fn ($kpis) => $kpis['completedCases'] === 1 && $kpis['revenue'] === 800000);
});

it('excludes activity outside the selected time window', function () {
    [$clinic, $owner] = seedAnalyticsClinic();
    $lead = Lead::create(['full_name' => 'Old', 'email' => 'o'.uniqid().'@e.com', 'status' => 'assigned']);
    // Assigned 200 days ago — outside a 30-day window, inside "all time".
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now()->subDays(200), 'responded_at' => now()->subDays(200)]);

    $component = Livewire::actingAs($owner)->test(ClinicAnalytics::class, ['clinic' => $clinic]);
    $component->assertViewHas('kpis', fn ($k) => $k['leads'] === 0);     // default 30d
    $component->set('range', 'all')->assertViewHas('kpis', fn ($k) => $k['leads'] === 1);
});

it('does not count another clinic\'s data', function () {
    [$clinic, $owner] = seedAnalyticsClinic();
    [$otherClinic] = seedAnalyticsClinic();
    $lead = Lead::create(['full_name' => 'X', 'email' => 'x'.uniqid().'@e.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $otherClinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    Livewire::actingAs($owner)
        ->test(ClinicAnalytics::class, ['clinic' => $clinic])
        ->assertViewHas('kpis', fn ($k) => $k['leads'] === 0);
});

it('computes the offer acceptance rate', function () {
    [$clinic, $owner] = seedAnalyticsClinic();
    $lead = Lead::create(['full_name' => 'O', 'email' => 'o'.uniqid().'@e.com', 'status' => 'assigned']);
    // 4 offers: 1 accepted -> 25%.
    Offer::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'A', 'price_total' => 1000, 'currency' => 'EUR', 'status' => 'accepted']);
    Offer::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'B', 'price_total' => 1000, 'currency' => 'EUR', 'status' => 'sent']);
    Offer::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'C', 'price_total' => 1000, 'currency' => 'EUR', 'status' => 'rejected']);
    Offer::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'D', 'price_total' => 1000, 'currency' => 'EUR', 'status' => 'viewed']);

    Livewire::actingAs($owner)
        ->test(ClinicAnalytics::class, ['clinic' => $clinic])
        ->assertViewHas('kpis', fn ($k) => $k['offerRate'] === 25.0);
});
