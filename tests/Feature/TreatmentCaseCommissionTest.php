<?php

declare(strict_types=1);

use App\Livewire\Admin\LeadDetail;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Commission;
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

function seedTreatmentCaseClinic(): Clinic
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'C'.$iso2, 'name' => 'Caseland '.$n, 'slug' => 'caseland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'case-istanbul-'.$n]);

    return Clinic::create([
        'slug' => 'case-clinic-'.uniqid(), 'name' => ['en' => 'Case Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

function seedAcceptedLeadForCase(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Case Patient', 'email' => 'case-patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('lets a sales agent create a treatment case for a clinic that accepted the lead', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->set('tcClinicId', $clinic->id)
        ->set('tcAgreedPrice', '1200.00')
        ->set('tcCurrency', 'EUR')
        ->call('createTreatmentCase')
        ->assertHasNoErrors();

    $case = TreatmentCase::where('lead_id', $lead->id)->first();
    expect($case)->not->toBeNull();
    expect($case->agreed_price)->toBe(120000);
    expect($case->status->value)->toBe('planned');
});

it('blocks creating a treatment case for a clinic that has not accepted the lead', function () {
    $clinic = seedTreatmentCaseClinic();
    $otherClinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->set('tcClinicId', $otherClinic->id)
        ->set('tcAgreedPrice', '1200.00')
        ->call('createTreatmentCase')
        ->assertForbidden();

    expect(TreatmentCase::count())->toBe(0);
});

it('prefills the treatment case form from an accepted offer', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $offer = Offer::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'Implant Plan',
        'price_total' => 150000, 'currency' => 'EUR', 'status' => 'accepted',
    ]);

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('loadFromOffer', $offer->id)
        ->assertSet('tcClinicId', $clinic->id)
        ->assertSet('tcAgreedPrice', '1500.00')
        ->assertSet('tcCurrency', 'EUR');
});

it('generates a commission and marks the lead won when the case is completed', function () {
    config(['clinicest.default_commission_rate' => 10.0]);

    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $case = TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id,
        'agreed_price' => 100000, 'currency' => 'EUR', 'status' => 'planned',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateTreatmentCaseStatus', 'completed')
        ->assertHasNoErrors();

    expect($case->fresh()->status->value)->toBe('completed');
    expect($lead->fresh()->status->value)->toBe('won');

    $commission = Commission::where('treatment_case_id', $case->id)->first();
    expect($commission)->not->toBeNull();
    expect($commission->base_amount)->toBe(100000);
    expect((float) $commission->rate_pct)->toBe(10.0);
    expect($commission->amount)->toBe(10000);
    expect($commission->status->value)->toBe('pending');
});

it('does not generate a second commission if the case is marked completed twice', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $case = TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id,
        'agreed_price' => 100000, 'currency' => 'EUR', 'status' => 'completed', 'completion_date' => now(),
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateTreatmentCaseStatus', 'completed');

    expect(Commission::where('treatment_case_id', $case->id)->count())->toBe(1);
});

it('lets finance update commission status but not treatment case status', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $case = TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id,
        'agreed_price' => 100000, 'currency' => 'EUR', 'status' => 'completed', 'completion_date' => now(),
    ]);
    $commission = Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id,
        'base_amount' => 100000, 'rate_pct' => 12.5, 'amount' => 12500, 'currency' => 'EUR', 'status' => 'pending',
    ]);

    $finance = User::factory()->create();
    $finance->assignRole('finance'); // access-admin, leads.view, billing.*, commissions.manage — no leads.manage

    Livewire::actingAs($finance)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateCommissionStatus', 'invoiced')
        ->assertHasNoErrors();

    expect($commission->fresh()->status->value)->toBe('invoiced');

    Livewire::actingAs($finance)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateTreatmentCaseStatus', 'in_treatment')
        ->assertForbidden();

    expect($case->fresh()->status->value)->toBe('completed');
});

it('blocks a sales agent without commissions.manage from updating commission status', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $case = TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id,
        'agreed_price' => 100000, 'currency' => 'EUR', 'status' => 'completed', 'completion_date' => now(),
    ]);
    Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id,
        'base_amount' => 100000, 'rate_pct' => 12.5, 'amount' => 12500, 'currency' => 'EUR', 'status' => 'pending',
    ]);

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateCommissionStatus', 'paid')
        ->assertForbidden();
});

it('marks a paid commission with a paid_at timestamp', function () {
    $clinic = seedTreatmentCaseClinic();
    $lead = seedAcceptedLeadForCase($clinic);
    $case = TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id,
        'agreed_price' => 100000, 'currency' => 'EUR', 'status' => 'completed', 'completion_date' => now(),
    ]);
    $commission = Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id,
        'base_amount' => 100000, 'rate_pct' => 12.5, 'amount' => 12500, 'currency' => 'EUR', 'status' => 'invoiced',
    ]);

    $finance = User::factory()->create();
    $finance->assignRole('finance');

    Livewire::actingAs($finance)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateCommissionStatus', 'paid');

    expect($commission->fresh()->status->value)->toBe('paid');
    expect($commission->fresh()->paid_at)->not->toBeNull();
});
