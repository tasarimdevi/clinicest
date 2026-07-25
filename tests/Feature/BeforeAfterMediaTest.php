<?php

declare(strict_types=1);

use App\Livewire\Admin\BeforeAfterModeration;
use App\Livewire\Clinic\ClinicBeforeAfter;
use App\Models\BeforeAfterCase;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Treatment;
use App\Models\User;
use App\Notifications\BeforeAfterPendingModeration;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedBaClinic(string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'X'.$iso2, 'name' => 'Baland '.$n, 'slug' => 'baland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'ba-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'ba-clinic-'.uniqid(), 'name' => ['en' => 'BA Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);
    $user->assignRole($spatieRole);

    $treatment = Treatment::create(['slug' => 'ba-implants-'.uniqid(), 'name' => ['en' => 'BA Implants'], 'status' => 'published']);

    return [$clinic, $user, $treatment];
}

it('lets a clinic owner submit a before/after case, unpublished and awaiting moderation', function () {
    Notification::fake();
    Storage::fake('public');

    [$clinic, $owner, $treatment] = seedBaClinic();

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::actingAs($owner)
        ->test(ClinicBeforeAfter::class, ['clinic' => $clinic])
        ->set('before', UploadedFile::fake()->image('before.jpg'))
        ->set('after', UploadedFile::fake()->image('after.jpg'))
        ->set('title', 'Full arch, one week')
        ->set('treatment_id', $treatment->id)
        ->set('consent', true)
        ->call('submit')
        ->assertHasNoErrors();

    $case = BeforeAfterCase::first();
    expect($case)->not->toBeNull();
    expect($case->is_published)->toBeFalse();
    expect($case->consent_confirmed)->toBeTrue();
    expect($case->hasPhotos())->toBeTrue();
    Storage::disk('public')->assertExists($case->before_media_path);
    Storage::disk('public')->assertExists($case->after_media_path);

    Notification::assertSentTo($moderator, BeforeAfterPendingModeration::class);
});

it('requires the consent checkbox to submit a before/after case', function () {
    Storage::fake('public');
    [$clinic, $owner, $treatment] = seedBaClinic();

    Livewire::actingAs($owner)
        ->test(ClinicBeforeAfter::class, ['clinic' => $clinic])
        ->set('before', UploadedFile::fake()->image('before.jpg'))
        ->set('after', UploadedFile::fake()->image('after.jpg'))
        ->set('title', 'No consent')
        ->set('treatment_id', $treatment->id)
        ->set('consent', false)
        ->call('submit')
        ->assertHasErrors(['consent']);

    expect(BeforeAfterCase::count())->toBe(0);
});

it('requires both before and after photos', function () {
    Storage::fake('public');
    [$clinic, $owner, $treatment] = seedBaClinic();

    Livewire::actingAs($owner)
        ->test(ClinicBeforeAfter::class, ['clinic' => $clinic])
        ->set('before', UploadedFile::fake()->image('before.jpg'))
        ->set('title', 'Missing after')
        ->set('treatment_id', $treatment->id)
        ->set('consent', true)
        ->call('submit')
        ->assertHasErrors(['after']);

    expect(BeforeAfterCase::count())->toBe(0);
});

it('rejects a doctor that does not belong to the submitting clinic', function () {
    Storage::fake('public');
    [$clinic, $owner, $treatment] = seedBaClinic();
    [$otherClinic] = seedBaClinic();
    $foreignDoctor = Doctor::create(['clinic_id' => $otherClinic->id, 'full_name' => 'Dr. Foreign', 'slug' => 'dr-foreign-'.uniqid()]);

    Livewire::actingAs($owner)
        ->test(ClinicBeforeAfter::class, ['clinic' => $clinic])
        ->set('before', UploadedFile::fake()->image('before.jpg'))
        ->set('after', UploadedFile::fake()->image('after.jpg'))
        ->set('title', 'Foreign doctor')
        ->set('treatment_id', $treatment->id)
        ->set('doctor_id', $foreignDoctor->id)
        ->set('consent', true)
        ->call('submit')
        ->assertForbidden();

    expect(BeforeAfterCase::count())->toBe(0);
});

it('blocks a clinic manager without clinics.manage from the before/after page', function () {
    [$clinic, $manager] = seedBaClinic('clinic_manager');

    $this->actingAs($manager)
        ->get(route('clinic.before-after', $clinic))
        ->assertForbidden();
});

it('lets a moderator approve a case, publishing it', function () {
    Storage::fake('public');
    [$clinic, , $treatment] = seedBaClinic();
    $case = $clinic->beforeAfterCases()->create([
        'treatment_id' => $treatment->id, 'title' => ['en' => 'Pending case'],
        'before_media_path' => 'before-after/x/b.jpg', 'after_media_path' => 'before-after/x/a.jpg',
        'consent_confirmed' => true, 'is_published' => false,
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::actingAs($moderator)
        ->test(BeforeAfterModeration::class)
        ->call('approve', $case->id)
        ->assertHasNoErrors();

    expect($case->fresh()->is_published)->toBeTrue();
});

it('lets a moderator reject a case, deleting it and its files', function () {
    Storage::fake('public');
    [$clinic, , $treatment] = seedBaClinic();

    Storage::disk('public')->put('before-after/x/b.jpg', 'x');
    Storage::disk('public')->put('before-after/x/a.jpg', 'x');

    $case = $clinic->beforeAfterCases()->create([
        'treatment_id' => $treatment->id, 'title' => ['en' => 'Bad case'],
        'before_media_path' => 'before-after/x/b.jpg', 'after_media_path' => 'before-after/x/a.jpg',
        'consent_confirmed' => true, 'is_published' => false,
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::actingAs($moderator)
        ->test(BeforeAfterModeration::class)
        ->call('reject', $case->id)
        ->assertHasNoErrors();

    expect(BeforeAfterCase::find($case->id))->toBeNull();
    Storage::disk('public')->assertMissing('before-after/x/b.jpg');
    Storage::disk('public')->assertMissing('before-after/x/a.jpg');
});

it('blocks a user without reviews.moderate from the moderation queue', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $this->actingAs($agent)->get(route('admin.before-after.index'))->assertForbidden();
});

it('only shows published before/after cases with photos on the public clinic page', function () {
    Storage::fake('public');
    [$clinic, , $treatment] = seedBaClinic();

    $clinic->beforeAfterCases()->create([
        'treatment_id' => $treatment->id, 'title' => ['en' => 'Published Visible Case'],
        'before_media_path' => 'before-after/x/b.jpg', 'after_media_path' => 'before-after/x/a.jpg',
        'consent_confirmed' => true, 'is_published' => true,
    ]);
    $clinic->beforeAfterCases()->create([
        'treatment_id' => $treatment->id, 'title' => ['en' => 'Pending Hidden Case'],
        'before_media_path' => 'before-after/x/b2.jpg', 'after_media_path' => 'before-after/x/a2.jpg',
        'consent_confirmed' => true, 'is_published' => false,
    ]);

    $this->get(route('clinics.show', $clinic->slug))
        ->assertOk()
        ->assertSee('Published Visible Case')
        ->assertDontSee('Pending Hidden Case');
});
