<?php

declare(strict_types=1);

use App\Livewire\Admin\DoctorForm;
use App\Livewire\Admin\Doctors;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedClinicForDoctors(): Clinic
{
    $city = seedCity();

    return Clinic::create([
        'slug' => 'clinic-'.uniqid(),
        'name' => ['en' => 'Test Clinic'],
        'city_id' => $city->id,
        'verification_tier' => 'verified',
    ]);
}

it('blocks users without doctors.view from the doctors list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access-admin');

    $this->actingAs($user)->get(route('admin.doctors.index'))->assertForbidden();
});

it('lets an admin view and filter the doctors list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $clinic = seedClinicForDoctors();
    Doctor::create(['clinic_id' => $clinic->id, 'full_name' => 'Dr. Alice Smith', 'slug' => 'dr-alice-smith']);
    Doctor::create(['clinic_id' => $clinic->id, 'full_name' => 'Dr. Bob Jones', 'slug' => 'dr-bob-jones']);

    Livewire::actingAs($admin)
        ->test(Doctors::class)
        ->assertSee('Dr. Alice Smith')
        ->assertSee('Dr. Bob Jones')
        ->set('search', 'Alice')
        ->assertSee('Dr. Alice Smith')
        ->assertDontSee('Dr. Bob Jones');
});

it('lets an admin toggle a doctor featured', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $clinic = seedClinicForDoctors();
    $doctor = Doctor::create(['clinic_id' => $clinic->id, 'full_name' => 'Dr. Alice Smith', 'slug' => 'dr-alice-smith', 'is_featured' => false]);

    Livewire::actingAs($admin)
        ->test(Doctors::class)
        ->call('toggleFeatured', $doctor->id);

    expect($doctor->fresh()->is_featured)->toBeTrue();
});

it('lets a user with doctors.manage create a doctor', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $clinic = seedClinicForDoctors();

    Livewire::actingAs($admin)
        ->test(DoctorForm::class)
        ->set('full_name', 'Dr. New Doctor')
        ->set('slug', 'dr-new-doctor')
        ->set('clinic_id', $clinic->id)
        ->call('save');

    expect(Doctor::where('full_name', 'Dr. New Doctor')->exists())->toBeTrue();
});

it('blocks a user without doctors.manage from creating a doctor', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    // mount()-time authorization failures on a full-page Livewire component
    // render as a real 403 response rather than a raw thrown exception —
    // assert against the actual route (see AdminClinicsTest for the same note).
    $this->actingAs($agent)->get(route('admin.doctors.create'))->assertForbidden();
});

it('blocks a user without doctors.manage from editing an existing doctor', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $clinic = seedClinicForDoctors();
    $doctor = Doctor::create(['clinic_id' => $clinic->id, 'full_name' => 'Dr. Alice Smith', 'slug' => 'dr-alice-smith']);

    $this->actingAs($agent)->get(route('admin.doctors.edit', $doctor))->assertForbidden();
});
