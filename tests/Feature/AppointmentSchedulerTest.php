<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Clinic\AppointmentScheduler;
use App\Models\Appointment;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

function seedAppointmentClinic(?string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'P'.$iso2, 'name' => 'Apptland '.$n, 'slug' => 'apptland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'appt-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'appt-clinic-'.uniqid(), 'name' => ['en' => 'Appointment Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);

    // Always seed permissions so `appointments.manage` exists in the DB —
    // only conditionally assign a role, so the "no permission" test
    // exercises a real permission-denied check.
    (new RolePermissionSeeder)->run();
    if ($spatieRole) {
        $user->assignRole($spatieRole);
    }

    return [$clinic, $user];
}

function seedAcceptedLeadForAppointment(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Patient', 'email' => 'patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('lets an authorized clinic member request a remote consultation', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'remote_consult')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->set('meeting_url', 'https://meet.example.com/room')
        ->call('request')
        ->assertHasNoErrors();

    $appointment = Appointment::first();

    expect($appointment)->not->toBeNull();
    expect($appointment->status)->toBe(AppointmentStatus::Requested);
    expect($appointment->type->value)->toBe('remote_consult');
    expect($appointment->meeting_url)->toBe('https://meet.example.com/room');
    expect($lead->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('requires a meeting url for a remote consultation', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'remote_consult')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->set('meeting_url', '')
        ->call('request')
        ->assertHasErrors(['meeting_url']);

    expect(Appointment::count())->toBe(0);
});

it('does not require a meeting url for an on-site visit', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'onsite')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->call('request')
        ->assertHasNoErrors();

    expect(Appointment::count())->toBe(1);
});

it('blocks a clinic member without appointments.manage from reaching the scheduler', function () {
    [$clinic, $staffWithoutPermission] = seedAppointmentClinic(spatieRole: null);
    $lead = seedAcceptedLeadForAppointment($clinic);

    $this->actingAs($staffWithoutPermission)
        ->get(route('clinic.appointments.index', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('blocks scheduling for a lead the clinic has not accepted yet', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = Lead::create(['full_name' => 'Patient', 'email' => 'p@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    $this->actingAs($owner)
        ->get(route('clinic.appointments.index', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('lets the clinic confirm an appointment it requested', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    $component = Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'onsite')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->call('request');

    $appointment = Appointment::first();

    $component->call('updateStatus', $appointment->id, 'confirmed')->assertHasNoErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('lets an authorized admin update an appointment status from the lead detail page', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'onsite')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->call('request');

    $appointment = Appointment::first();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateAppointmentStatus', $appointment->id, 'completed')
        ->assertHasNoErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Completed);
});

it('blocks a user without appointments.manage from updating an appointment status', function () {
    [$clinic, $owner] = seedAppointmentClinic();
    $lead = seedAcceptedLeadForAppointment($clinic);

    Livewire::actingAs($owner)
        ->test(AppointmentScheduler::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('type', 'onsite')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('timezone', 'Europe/Istanbul')
        ->call('request');

    $appointment = Appointment::first();

    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    Livewire::actingAs($agent)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->call('updateAppointmentStatus', $appointment->id, 'completed')
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Requested);
});
