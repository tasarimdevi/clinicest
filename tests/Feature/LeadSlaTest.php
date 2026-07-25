<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Treatment;
use App\Models\User;
use App\Notifications\LeadAssignedToClinic;
use App\Notifications\LeadSlaBreached;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedSlaCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'Z'.$iso2, 'name' => 'Slaland '.$n, 'slug' => 'slaland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'sla-istanbul-'.$n]);
}

function seedSlaClinic(?Treatment $treatment = null): Clinic
{
    $city = seedSlaCity();
    $clinic = Clinic::create([
        'slug' => 'sla-clinic-'.uniqid(), 'name' => ['en' => 'SLA Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    if ($treatment) {
        $clinic->treatments()->attach($treatment->id, ['currency' => 'EUR', 'is_available' => true]);
    }

    return $clinic;
}

it('expires an offered assignment whose SLA has lapsed', function () {
    $clinic = seedSlaClinic();
    $lead = Lead::create(['full_name' => 'Late Lead', 'email' => 'late'.uniqid().'@e.com', 'status' => 'assigned']);
    $assignment = $lead->assignments()->create([
        'clinic_id' => $clinic->id, 'status' => 'offered',
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    Artisan::call('leads:enforce-sla');

    expect($assignment->fresh()->status)->toBe('expired');
});

it('does not touch an assignment still within its SLA window', function () {
    $clinic = seedSlaClinic();
    $lead = Lead::create(['full_name' => 'On Time', 'email' => 'ontime'.uniqid().'@e.com', 'status' => 'assigned']);
    $assignment = $lead->assignments()->create([
        'clinic_id' => $clinic->id, 'status' => 'offered',
        'assigned_at' => now(), 'sla_due_at' => now()->addHours(20),
    ]);

    Artisan::call('leads:enforce-sla');

    expect($assignment->fresh()->status)->toBe('offered');
});

it('does not touch an assignment the clinic already accepted', function () {
    $clinic = seedSlaClinic();
    $lead = Lead::create(['full_name' => 'Accepted', 'email' => 'acc'.uniqid().'@e.com', 'status' => 'assigned']);
    $assignment = $lead->assignments()->create([
        'clinic_id' => $clinic->id, 'status' => 'accepted', 'responded_at' => now()->subHour(),
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    Artisan::call('leads:enforce-sla');

    expect($assignment->fresh()->status)->toBe('accepted');
});

it('auto-reassigns a breached lead to the next eligible clinic and notifies both audiences', function () {
    Notification::fake();

    $treatment = Treatment::create(['slug' => 'sla-implants-'.uniqid(), 'name' => ['en' => 'SLA Implants'], 'status' => 'published']);
    $lapsed = seedSlaClinic($treatment);
    $next = seedSlaClinic($treatment);

    // A clinic owner at the candidate clinic, to receive the assignment notice.
    $nextOwner = User::factory()->create();
    $nextOwner->assignRole('clinic_owner');
    $next->users()->attach($nextOwner->id, ['role' => 'owner']);

    // A sales agent to receive the breach notice.
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $lead = Lead::create([
        'full_name' => 'Reassign Me', 'email' => 'r'.uniqid().'@e.com', 'status' => 'assigned',
        'primary_treatment_id' => $treatment->id,
    ]);
    $lead->assignments()->create([
        'clinic_id' => $lapsed->id, 'status' => 'offered',
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    Artisan::call('leads:enforce-sla');

    // Old expired, new offered to the next clinic.
    expect($lead->assignments()->where('clinic_id', $lapsed->id)->first()->status)->toBe('expired');
    $reassigned = $lead->assignments()->where('clinic_id', $next->id)->first();
    expect($reassigned)->not->toBeNull();
    expect($reassigned->status)->toBe('offered');
    expect($reassigned->assigned_by)->toBeNull();
    expect($lead->fresh()->status)->toBe(LeadStatus::Assigned);

    Notification::assertSentTo($nextOwner, LeadAssignedToClinic::class);
    Notification::assertSentTo($agent, LeadSlaBreached::class);
});

it('returns a breached lead to the assignable pool when no eligible clinic remains', function () {
    Notification::fake();

    $treatment = Treatment::create(['slug' => 'sla-only-'.uniqid(), 'name' => ['en' => 'SLA Only'], 'status' => 'published']);
    $onlyClinic = seedSlaClinic($treatment);

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $lead = Lead::create([
        'full_name' => 'Nowhere To Go', 'email' => 'n'.uniqid().'@e.com', 'status' => 'assigned',
        'primary_treatment_id' => $treatment->id,
    ]);
    $lead->assignments()->create([
        'clinic_id' => $onlyClinic->id, 'status' => 'offered',
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    Artisan::call('leads:enforce-sla');

    // No other clinic offers this treatment, so it drops back to Qualified.
    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
    expect($lead->assignments()->whereIn('status', ['offered', 'accepted'])->exists())->toBeFalse();

    Notification::assertSentTo($agent, LeadSlaBreached::class);
});

it('exhausts the candidate pool rather than bouncing forever', function () {
    $treatment = Treatment::create(['slug' => 'sla-two-'.uniqid(), 'name' => ['en' => 'SLA Two'], 'status' => 'published']);
    $a = seedSlaClinic($treatment);
    $b = seedSlaClinic($treatment);

    $lead = Lead::create([
        'full_name' => 'Bouncer', 'email' => 'b'.uniqid().'@e.com', 'status' => 'assigned',
        'primary_treatment_id' => $treatment->id,
    ]);
    $lead->assignments()->create([
        'clinic_id' => $a->id, 'status' => 'offered',
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    // First run: A expires, reassigned to B.
    Artisan::call('leads:enforce-sla');
    // Force B's new assignment overdue, then run again: B expires, no one left.
    $lead->assignments()->where('clinic_id', $b->id)->update(['sla_due_at' => now()->subHour()]);
    Artisan::call('leads:enforce-sla');

    expect($lead->assignments()->count())->toBe(2);
    expect($lead->assignments()->where('status', 'expired')->count())->toBe(2);
    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
});

it('expires but does not reassign a lead that is already won or lost', function () {
    Notification::fake();

    $treatment = Treatment::create(['slug' => 'sla-won-'.uniqid(), 'name' => ['en' => 'SLA Won'], 'status' => 'published']);
    $lapsed = seedSlaClinic($treatment);
    seedSlaClinic($treatment); // an eligible clinic exists, but the lead is closed

    $lead = Lead::create([
        'full_name' => 'Closed', 'email' => 'c'.uniqid().'@e.com', 'status' => 'won',
        'primary_treatment_id' => $treatment->id,
    ]);
    $lead->assignments()->create([
        'clinic_id' => $lapsed->id, 'status' => 'offered',
        'assigned_at' => now()->subHours(30), 'sla_due_at' => now()->subHours(6),
    ]);

    Artisan::call('leads:enforce-sla');

    expect($lead->assignments()->where('clinic_id', $lapsed->id)->first()->status)->toBe('expired');
    expect($lead->assignments()->count())->toBe(1); // no reassignment
    expect($lead->fresh()->status)->toBe(LeadStatus::Won);
});
