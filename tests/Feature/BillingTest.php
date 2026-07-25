<?php

declare(strict_types=1);

use App\Actions\Billing\AssignSubscription;
use App\Actions\Billing\GenerateInvoice;
use App\Actions\Billing\RecordPayment;
use App\Enums\SubscriptionStatus;
use App\Livewire\Admin\Billing;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Clinic\ClinicBilling;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\SubscriptionPlan;
use App\Models\TreatmentCase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
    (new SubscriptionPlanSeeder)->run();
});

function seedBillingClinic(): Clinic
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'Y'.$iso2, 'name' => 'Billland '.$n, 'slug' => 'billland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'bill-istanbul-'.$n]);

    return Clinic::create([
        'slug' => 'bill-clinic-'.uniqid(), 'name' => ['en' => 'Billing Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

it('seeds the three subscription plan tiers', function () {
    expect(SubscriptionPlan::pluck('slug')->sort()->values()->all())->toBe(['elite', 'growth', 'verified']);
});

it('assigns a plan to a clinic and cancels any previous live subscription', function () {
    $clinic = seedBillingClinic();
    $growth = SubscriptionPlan::where('slug', 'growth')->firstOrFail();
    $elite = SubscriptionPlan::where('slug', 'elite')->firstOrFail();

    $action = app(AssignSubscription::class);
    $first = $action->handle($clinic, $growth);
    $second = $action->handle($clinic, $elite);

    expect($first->fresh()->status)->toBe(SubscriptionStatus::Canceled);
    expect($second->status)->toBe(SubscriptionStatus::Active);
    expect($clinic->activeSubscription->plan_id)->toBe($elite->id);
    expect($clinic->subscriptions()->count())->toBe(2);
});

it('generates an invoice for a commission with the commission amount', function () {
    $clinic = seedBillingClinic();
    $lead = Lead::create(['full_name' => 'P', 'email' => 'p'.uniqid().'@e.com', 'status' => 'won']);
    $case = TreatmentCase::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 500000, 'currency' => 'EUR', 'status' => 'completed']);
    $commission = Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id, 'base_amount' => 500000,
        'rate_pct' => 12.5, 'amount' => 62500, 'currency' => 'EUR', 'status' => 'pending',
    ]);

    $invoice = app(GenerateInvoice::class)->handle($clinic, $commission);

    expect($invoice->total)->toBe(62500);
    expect($invoice->currency)->toBe('EUR');
    expect($invoice->status->value)->toBe('sent');
    expect($invoice->billable_type)->toBe(Commission::class);
    expect($invoice->billable_id)->toBe($commission->id);
    expect($invoice->number)->toStartWith('INV-');
});

it('records a payment that marks the invoice paid and settles the underlying commission', function () {
    $clinic = seedBillingClinic();
    $lead = Lead::create(['full_name' => 'P', 'email' => 'p'.uniqid().'@e.com', 'status' => 'won']);
    $case = TreatmentCase::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 500000, 'currency' => 'EUR', 'status' => 'completed']);
    $commission = Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id, 'base_amount' => 500000,
        'rate_pct' => 12.5, 'amount' => 62500, 'currency' => 'EUR', 'status' => 'invoiced',
    ]);
    $invoice = app(GenerateInvoice::class)->handle($clinic, $commission);
    $commission->update(['invoice_id' => $invoice->id]);

    $payment = app(RecordPayment::class)->handle($invoice);

    expect($payment->amount)->toBe(62500);
    expect($payment->status->value)->toBe('succeeded');
    expect($invoice->fresh()->status->value)->toBe('paid');
    expect($commission->fresh()->status->value)->toBe('paid');
});

it('lets finance mark an invoice paid from the admin billing desk', function () {
    $clinic = seedBillingClinic();
    $invoice = Invoice::create([
        'number' => 'INV-TEST-1', 'clinic_id' => $clinic->id, 'amount' => 19900, 'tax' => 0,
        'total' => 19900, 'currency' => 'EUR', 'status' => 'sent', 'issued_at' => now(),
    ]);

    $finance = User::factory()->create();
    $finance->assignRole('finance');

    Livewire::actingAs($finance)
        ->test(Billing::class)
        ->call('markPaid', $invoice->id)
        ->assertHasNoErrors();

    expect($invoice->fresh()->status->value)->toBe('paid');
    expect($invoice->payments()->count())->toBe(1);
});

it('lets finance assign a subscription plan from the admin billing desk', function () {
    $clinic = seedBillingClinic();
    $growth = SubscriptionPlan::where('slug', 'growth')->firstOrFail();

    $finance = User::factory()->create();
    $finance->assignRole('finance');

    Livewire::actingAs($finance)
        ->test(Billing::class)
        ->set("planFor.{$clinic->id}", $growth->id)
        ->call('assignPlan', $clinic->id)
        ->assertHasNoErrors();

    expect($clinic->activeSubscription?->plan_id)->toBe($growth->id);
});

it('blocks a user without billing.view from the admin billing desk', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent'); // access-admin, no billing.view

    $this->actingAs($agent)->get(route('admin.billing.index'))->assertForbidden();
});

it('blocks a billing.view-only user from marking an invoice paid', function () {
    // clinic_owner has billing.view but not billing.manage, and also no
    // access-admin — so it can't reach the admin desk at all; simulate a
    // view-only internal user by granting just access-admin + billing.view.
    $clinic = seedBillingClinic();
    $invoice = Invoice::create([
        'number' => 'INV-TEST-2', 'clinic_id' => $clinic->id, 'amount' => 19900, 'tax' => 0,
        'total' => 19900, 'currency' => 'EUR', 'status' => 'sent', 'issued_at' => now(),
    ]);

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('access-admin', 'billing.view');

    Livewire::actingAs($viewer)
        ->test(Billing::class)
        ->call('markPaid', $invoice->id)
        ->assertForbidden();

    expect($invoice->fresh()->status->value)->toBe('sent');
});

it('generates and links an invoice when a commission is marked invoiced from the lead detail page', function () {
    $clinic = seedBillingClinic();
    $lead = Lead::create(['full_name' => 'Invoiced Patient', 'email' => 'inv'.uniqid().'@e.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);
    $case = TreatmentCase::create(['lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 400000, 'currency' => 'EUR', 'status' => 'completed']);
    $commission = Commission::create([
        'treatment_case_id' => $case->id, 'clinic_id' => $clinic->id, 'base_amount' => 400000,
        'rate_pct' => 12.5, 'amount' => 50000, 'currency' => 'EUR', 'status' => 'pending',
    ]);

    $finance = User::factory()->create();
    $finance->assignRole('admin');

    Livewire::actingAs($finance)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateCommissionStatus', 'invoiced')
        ->assertHasNoErrors();

    $commission->refresh();
    expect($commission->status->value)->toBe('invoiced');
    expect($commission->invoice_id)->not->toBeNull();
    expect($commission->invoice->total)->toBe(50000);

    // Idempotent: re-invoicing doesn't create a second invoice.
    $existingInvoiceId = $commission->invoice_id;
    Livewire::actingAs($finance)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateCommissionStatus', 'invoiced');
    expect($commission->fresh()->invoice_id)->toBe($existingInvoiceId);
    expect(Invoice::count())->toBe(1);
});

it('shows the clinic its own subscription and invoices, scoped to itself', function () {
    $clinic = seedBillingClinic();
    $other = seedBillingClinic();
    $growth = SubscriptionPlan::where('slug', 'growth')->firstOrFail();
    app(AssignSubscription::class)->handle($clinic, $growth);

    Invoice::create(['number' => 'INV-MINE-1', 'clinic_id' => $clinic->id, 'amount' => 19900, 'tax' => 0, 'total' => 19900, 'currency' => 'EUR', 'status' => 'sent', 'issued_at' => now()]);
    Invoice::create(['number' => 'INV-OTHER-1', 'clinic_id' => $other->id, 'amount' => 49900, 'tax' => 0, 'total' => 49900, 'currency' => 'EUR', 'status' => 'sent', 'issued_at' => now()]);

    $owner = User::factory()->create();
    $owner->assignRole('clinic_owner');
    $clinic->users()->attach($owner->id, ['role' => 'owner']);

    Livewire::actingAs($owner)
        ->test(ClinicBilling::class, ['clinic' => $clinic])
        ->assertSee('Growth')
        ->assertSee('INV-MINE-1')
        ->assertDontSee('INV-OTHER-1');
});

it('blocks reaching another clinic billing page you are not a member of', function () {
    $clinicA = seedBillingClinic();
    $clinicB = seedBillingClinic();

    $owner = User::factory()->create();
    $owner->assignRole('clinic_owner');
    $clinicA->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner)->get(route('clinic.billing', $clinicB))->assertForbidden();
});
