<?php

declare(strict_types=1);

use App\Livewire\Clinic\ClinicProfile;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Treatment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedProfileClinic(?string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'P'.$iso2, 'name' => 'Profileland '.$n, 'slug' => 'profileland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'profile-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'profile-clinic-'.uniqid(), 'name' => ['en' => 'Profile Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);

    if ($spatieRole) {
        $user->assignRole($spatieRole);
    }

    return [$clinic, $user];
}

it('lets a clinic owner update their basic profile info', function () {
    [$clinic, $owner] = seedProfileClinic();

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('name.en', 'Updated Clinic Name')
        ->set('name.tr', 'Güncellenen Klinik Adı')
        ->set('about.en', 'A brand new description.')
        ->set('phone', '+90 212 111 2233')
        ->set('languages', ['en', 'tr'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    $clinic->refresh();
    expect($clinic->getTranslation('name', 'en'))->toBe('Updated Clinic Name');
    expect($clinic->getTranslation('name', 'tr'))->toBe('Güncellenen Klinik Adı');
    expect($clinic->getTranslation('about', 'en'))->toBe('A brand new description.');
    expect($clinic->phone)->toBe('+90 212 111 2233');
    expect($clinic->languages_json)->toBe(['en', 'tr']);
});

it('blocks a clinic manager (clinics.view only) from reaching the profile editor', function () {
    [$clinic, $manager] = seedProfileClinic('clinic_manager'); // clinics.view, not clinics.manage

    $this->actingAs($manager)
        ->get(route('clinic.profile', $clinic))
        ->assertForbidden();
});

it('lets a clinic owner add a treatment with pricing', function () {
    [$clinic, $owner] = seedProfileClinic();
    $treatment = Treatment::create(['slug' => 'cp-implants', 'name' => ['en' => 'CP Implants'], 'currency' => 'EUR', 'status' => 'published']);

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newTreatmentId', $treatment->id)
        ->set('newPriceMin', '450.00')
        ->set('newPriceMax', '900.00')
        ->set('newCurrency', 'eur')
        ->call('addTreatment')
        ->assertHasNoErrors();

    $pivot = $clinic->treatments()->where('treatment_id', $treatment->id)->first()->pivot;
    expect($pivot->price_min)->toBe(45000);
    expect($pivot->price_max)->toBe(90000);
    expect($pivot->currency)->toBe('EUR');
    expect((bool) $pivot->is_available)->toBeTrue();
});

it('rejects adding a treatment when the max price is below the min price', function () {
    [$clinic, $owner] = seedProfileClinic();
    $treatment = Treatment::create(['slug' => 'cp-veneers', 'name' => ['en' => 'CP Veneers'], 'currency' => 'EUR', 'status' => 'published']);

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newTreatmentId', $treatment->id)
        ->set('newPriceMin', '900.00')
        ->set('newPriceMax', '450.00')
        ->call('addTreatment')
        ->assertHasErrors(['newPriceMax']);

    expect($clinic->treatments()->count())->toBe(0);
});

it('lets a clinic owner update an existing treatment price', function () {
    [$clinic, $owner] = seedProfileClinic();
    $treatment = Treatment::create(['slug' => 'cp-whitening', 'name' => ['en' => 'CP Whitening'], 'currency' => 'EUR', 'status' => 'published']);
    $clinic->treatments()->attach($treatment->id, ['price_min' => 10000, 'price_max' => 15000, 'currency' => 'EUR', 'is_available' => true]);

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set("prices.{$treatment->id}.min", '120.00')
        ->set("prices.{$treatment->id}.max", '180.00')
        ->set("prices.{$treatment->id}.currency", 'EUR')
        ->call('updateTreatmentPrice', $treatment->id)
        ->assertHasNoErrors();

    $pivot = $clinic->treatments()->where('treatment_id', $treatment->id)->first()->pivot;
    expect($pivot->price_min)->toBe(12000);
    expect($pivot->price_max)->toBe(18000);
});

it('lets a clinic owner toggle treatment availability and remove a treatment', function () {
    [$clinic, $owner] = seedProfileClinic();
    $treatment = Treatment::create(['slug' => 'cp-allon4', 'name' => ['en' => 'CP All-on-4'], 'currency' => 'EUR', 'status' => 'published']);
    $clinic->treatments()->attach($treatment->id, ['price_min' => 100000, 'price_max' => 150000, 'currency' => 'EUR', 'is_available' => true]);

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);

    $component->call('toggleTreatmentAvailability', $treatment->id);
    expect((bool) $clinic->treatments()->where('treatment_id', $treatment->id)->first()->pivot->is_available)->toBeFalse();

    $component->call('removeTreatment', $treatment->id);
    expect($clinic->treatments()->where('treatment_id', $treatment->id)->exists())->toBeFalse();
});

it('does not let a user manage a clinic they do not belong to', function () {
    [$clinicA] = seedProfileClinic();
    [, $ownerB] = seedProfileClinic();

    $this->actingAs($ownerB)
        ->get(route('clinic.profile', $clinicA))
        ->assertForbidden();
});

it('lets a clinic owner upload a gallery photo, which becomes the cover automatically', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newMedia', UploadedFile::fake()->image('front-desk.jpg'))
        ->set('newMediaCaption', 'Our front desk')
        ->call('uploadMedia')
        ->assertHasNoErrors();

    $media = $clinic->media()->first();
    expect($media)->not->toBeNull();
    expect($media->is_cover)->toBeTrue();
    expect($media->caption)->toBe('Our front desk');
    Storage::disk('public')->assertExists($media->path);
});

it('generates webp + thumbnail variants and records dimensions when a gallery photo is uploaded', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newMedia', UploadedFile::fake()->image('wide.jpg', 3000, 2000))
        ->call('uploadMedia')
        ->assertHasNoErrors();

    $media = $clinic->media()->first();
    expect($media->width)->toBe(1600);
    expect($media->height)->toBe(1067);
    expect($media->variants_json)->toHaveKeys(['webp', 'thumb']);
    expect($media->webp_url)->toContain('/storage/');
    Storage::disk('public')->assertExists($media->variants_json['webp']);
    Storage::disk('public')->assertExists($media->variants_json['thumb']);
});

it('deletes variant files too when a gallery photo is removed', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);
    $component->set('newMedia', UploadedFile::fake()->image('one.jpg'))->call('uploadMedia');

    $media = $clinic->media()->firstOrFail();
    $paths = [$media->path, $media->variants_json['webp'], $media->variants_json['thumb']];

    $component->call('deleteMedia', $media->id);

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

it('lets a clinic owner reorder gallery photos with the move arrows', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);
    $component->set('newMedia', UploadedFile::fake()->image('first.jpg'))->call('uploadMedia');
    $component->set('newMedia', UploadedFile::fake()->image('second.jpg'))->call('uploadMedia');

    $first = $clinic->media()->orderBy('sort')->orderBy('id')->first();
    $second = $clinic->media()->orderBy('sort')->orderBy('id')->skip(1)->first();
    expect($first->sort)->toBeLessThan($second->sort);

    // Move the second photo up one position — it should now sort first.
    $component->call('moveMedia', $second->id, -1);

    $ordered = $clinic->media()->orderBy('sort')->orderBy('id')->pluck('id')->all();
    expect($ordered[0])->toBe($second->id);
    expect($ordered[1])->toBe($first->id);
});

it('does not change the cover when a second photo is uploaded', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);

    $component->set('newMedia', UploadedFile::fake()->image('one.jpg'))->call('uploadMedia');
    $component->set('newMedia', UploadedFile::fake()->image('two.jpg'))->call('uploadMedia');

    expect($clinic->media()->where('is_cover', true)->count())->toBe(1);
    expect($clinic->media()->count())->toBe(2);
});

it('rejects a non-image upload for the gallery', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newMedia', UploadedFile::fake()->create('doc.pdf', 100))
        ->call('uploadMedia')
        ->assertHasErrors(['newMedia']);

    expect($clinic->media()->count())->toBe(0);
});

it('lets a clinic owner change the cover photo', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);
    $component->set('newMedia', UploadedFile::fake()->image('one.jpg'))->call('uploadMedia');
    $component->set('newMedia', UploadedFile::fake()->image('two.jpg'))->call('uploadMedia');

    $second = $clinic->media()->orderBy('sort')->skip(1)->firstOrFail();

    $component->call('setCoverMedia', $second->id)->assertHasNoErrors();

    expect($second->fresh()->is_cover)->toBeTrue();
    expect($clinic->media()->where('is_cover', true)->count())->toBe(1);
});

it('deletes a gallery photo and promotes another to cover if the deleted one was the cover', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    $component = Livewire::actingAs($owner)->test(ClinicProfile::class, ['clinic' => $clinic]);
    $component->set('newMedia', UploadedFile::fake()->image('one.jpg'))->call('uploadMedia');
    $component->set('newMedia', UploadedFile::fake()->image('two.jpg'))->call('uploadMedia');

    $cover = $clinic->media()->where('is_cover', true)->firstOrFail();
    $path = $cover->path;

    $component->call('deleteMedia', $cover->id)->assertHasNoErrors();

    Storage::disk('public')->assertMissing($path);
    expect($clinic->media()->count())->toBe(1);
    expect($clinic->media()->where('is_cover', true)->count())->toBe(1);
});

it('shows the clinic gallery on the public clinic profile page', function () {
    Storage::fake('public');
    [$clinic, $owner] = seedProfileClinic();

    Livewire::actingAs($owner)
        ->test(ClinicProfile::class, ['clinic' => $clinic])
        ->set('newMedia', UploadedFile::fake()->image('lobby.jpg'))
        ->call('uploadMedia');

    $media = $clinic->media()->firstOrFail();

    $this->get(route('clinics.show', $clinic->slug))
        ->assertOk()
        ->assertSee($media->url, false);
});
