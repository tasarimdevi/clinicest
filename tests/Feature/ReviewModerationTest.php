<?php

declare(strict_types=1);

use App\Livewire\Admin\ReviewModeration;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedModerationClinic(): Clinic
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'R'.$iso2, 'name' => 'Modland '.$n, 'slug' => 'modland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'mod-istanbul-'.$n]);

    return Clinic::create([
        'slug' => 'mod-clinic-'.uniqid(), 'name' => ['en' => 'Moderation Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

it('lets a moderator approve a pending review', function () {
    $clinic = seedModerationClinic();
    $review = Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id, 'reviewer_name' => 'Jane',
        'rating' => 5, 'body' => 'Great clinic.', 'is_verified' => false, 'status' => 'pending',
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::actingAs($moderator)
        ->test(ReviewModeration::class)
        ->call('approve', $review->id)
        ->assertHasNoErrors();

    expect($review->fresh()->status)->toBe('approved');
    expect($review->fresh()->moderated_by)->toBe($moderator->id);
});

it('lets a moderator reject a pending review', function () {
    $clinic = seedModerationClinic();
    $review = Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id, 'reviewer_name' => 'Jane',
        'rating' => 1, 'body' => 'Spam content.', 'is_verified' => false, 'status' => 'pending',
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::actingAs($moderator)
        ->test(ReviewModeration::class)
        ->call('reject', $review->id)
        ->assertHasNoErrors();

    expect($review->fresh()->status)->toBe('rejected');
});

it('blocks a user without reviews.moderate from reaching the queue', function () {
    $agent = User::factory()->create();
    $agent->assignRole('sales_agent');

    $this->actingAs($agent)
        ->get(route('admin.reviews.index'))
        ->assertForbidden();
});
