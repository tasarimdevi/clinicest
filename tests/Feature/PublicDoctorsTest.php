<?php

declare(strict_types=1);

use App\Livewire\Public\DoctorsIndex;
use App\Models\Clinic;
use App\Models\Doctor;
use Livewire\Livewire;

function seedActiveClinicForDoctors(): Clinic
{
    $city = seedPublicCity();

    return Clinic::create([
        'slug' => 'clinic-'.uniqid(), 'name' => ['en' => 'Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

it('renders the doctors directory for doctors at active clinics only', function () {
    $activeClinic = seedActiveClinicForDoctors();
    $city = seedPublicCity();
    $inactiveClinic = Clinic::create(['slug' => 'inactive-clinic', 'name' => ['en' => 'Inactive'], 'city_id' => $city->id, 'verification_tier' => 'pending', 'is_active' => false]);

    Doctor::create(['slug' => 'dr-active', 'clinic_id' => $activeClinic->id, 'full_name' => 'Dr. Active One']);
    Doctor::create(['slug' => 'dr-inactive', 'clinic_id' => $inactiveClinic->id, 'full_name' => 'Dr. Inactive One']);

    Livewire::test(DoctorsIndex::class)
        ->assertSee('Dr. Active One')
        ->assertDontSee('Dr. Inactive One');
});

it('filters the doctors directory by clinic', function () {
    $clinicA = seedActiveClinicForDoctors();
    $clinicB = seedActiveClinicForDoctors();

    Doctor::create(['slug' => 'dr-a', 'clinic_id' => $clinicA->id, 'full_name' => 'Dr. Alpha']);
    Doctor::create(['slug' => 'dr-b', 'clinic_id' => $clinicB->id, 'full_name' => 'Dr. Beta']);

    Livewire::test(DoctorsIndex::class)
        ->set('clinic', (string) $clinicA->id)
        ->assertSee('Dr. Alpha')
        ->assertDontSee('Dr. Beta');
});

it('renders a doctor profile page with clinic link', function () {
    $clinic = seedActiveClinicForDoctors();
    $doctor = Doctor::create([
        'slug' => 'dr-elif-kaya', 'clinic_id' => $clinic->id, 'full_name' => 'Dr. Elif Kaya',
        'specialty' => ['en' => 'Prosthodontics'], 'bio' => ['en' => 'Experienced dentist.'],
    ]);

    $this->get(route('doctors.show', $doctor->slug))
        ->assertOk()
        ->assertSee('Dr. Elif Kaya')
        ->assertSee('Prosthodontics')
        ->assertSee($clinic->getTranslation('name', 'en'));
});
