<?php

declare(strict_types=1);

use App\Livewire\Clinic\LeadInbox;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

function seedClinicWithMember(string $role = 'staff'): array
{
    static $n = 0;
    $n++;

    // iso2/iso3 columns are fixed-width (2/3 chars) — derive short, unique,
    // in-bounds codes from the counter instead of a variable-length suffix.
    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $iso3 = 'A'.$iso2;

    $country = Country::create([
        'iso2' => $iso2, 'iso3' => $iso3, 'name' => 'Testland '.$n, 'slug' => 'testland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'test-clinic-'.uniqid(), 'name' => ['en' => 'Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => $role]);

    return [$clinic, $user];
}

it('blocks a user who is not a member of the clinic', function () {
    [$clinic] = seedClinicWithMember();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get(route('clinic.leads', $clinic))->assertForbidden();
});

it('shows only leads assigned to this clinic', function () {
    [$clinic, $staff] = seedClinicWithMember();
    [$otherClinic] = seedClinicWithMember();

    $ourLead = Lead::create(['full_name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'assigned']);
    $ourLead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    $otherLead = Lead::create(['full_name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'assigned']);
    $otherLead->assignments()->create(['clinic_id' => $otherClinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    Livewire::actingAs($staff)
        ->test(LeadInbox::class, ['clinic' => $clinic])
        ->assertSee('Alice')
        ->assertDontSee('Bob');
});

it('lets a clinic member accept an assigned lead', function () {
    [$clinic, $staff] = seedClinicWithMember();

    $lead = Lead::create(['full_name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'assigned']);
    $assignment = $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    Livewire::actingAs($staff)
        ->test(LeadInbox::class, ['clinic' => $clinic])
        ->call('accept', $assignment->id);

    expect($assignment->fresh()->status)->toBe('accepted');
    expect($lead->activities()->where('type', 'assignment')->exists())->toBeTrue();
});

it('does not let a clinic accept an assignment belonging to another clinic', function () {
    [$clinic, $staff] = seedClinicWithMember();
    [$otherClinic] = seedClinicWithMember();

    $lead = Lead::create(['full_name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'assigned']);
    $foreignAssignment = $lead->assignments()->create(['clinic_id' => $otherClinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    expect(fn () => Livewire::actingAs($staff)
        ->test(LeadInbox::class, ['clinic' => $clinic])
        ->call('accept', $foreignAssignment->id)
    )->toThrow(ModelNotFoundException::class);

    expect($foreignAssignment->fresh()->status)->toBe('offered');
});
