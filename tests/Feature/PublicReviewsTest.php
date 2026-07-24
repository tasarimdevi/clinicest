<?php

declare(strict_types=1);

use App\Livewire\Public\ReviewsIndex;
use App\Models\Clinic;
use App\Models\Review;
use App\Models\Treatment;
use Livewire\Livewire;

function seedActiveClinicForReviews(): Clinic
{
    $city = seedPublicCity();

    return Clinic::create([
        'slug' => 'rv-clinic-'.uniqid(), 'name' => ['en' => 'Review Test Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true, 'rating_avg' => 4.5, 'rating_count' => 2,
    ]);
}

it('shows only approved reviews on the reviews hub', function () {
    $clinic = seedActiveClinicForReviews();

    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id,
        'reviewer_name' => 'Approved Reviewer', 'rating' => 5, 'body' => 'Great experience.', 'status' => 'approved',
    ]);
    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id,
        'reviewer_name' => 'Pending Reviewer', 'rating' => 5, 'body' => 'Not yet moderated.', 'status' => 'pending',
    ]);

    Livewire::test(ReviewsIndex::class)
        ->assertSee('Approved Reviewer')
        ->assertDontSee('Pending Reviewer');
});

it('filters the reviews hub by clinic, treatment, and rating', function () {
    $clinicA = seedActiveClinicForReviews();
    $clinicB = seedActiveClinicForReviews();
    $treatment = Treatment::create(['slug' => 'implants', 'name' => ['en' => 'Implants'], 'status' => 'published']);

    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinicA->id, 'treatment_id' => $treatment->id,
        'reviewer_name' => 'Alice Match', 'rating' => 5, 'body' => 'Loved it.', 'status' => 'approved',
    ]);
    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinicB->id,
        'reviewer_name' => 'Bob NoMatch', 'rating' => 3, 'body' => 'It was fine.', 'status' => 'approved',
    ]);

    Livewire::test(ReviewsIndex::class)
        ->set('clinic', (string) $clinicA->id)
        ->assertSee('Alice Match')
        ->assertDontSee('Bob NoMatch');
});

it('renders a clinic reviews page with a rating breakdown', function () {
    $clinic = seedActiveClinicForReviews();

    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id,
        'reviewer_name' => 'Five Star', 'rating' => 5, 'title' => 'Excellent', 'body' => 'Perfect visit.',
        'is_verified' => true, 'status' => 'approved',
    ]);

    $this->get(route('reviews.show', $clinic->slug))
        ->assertOk()
        ->assertSee('Five Star')
        ->assertSee('Excellent')
        ->assertSee('Verified treatment');
});

it('404s for reviews of an inactive clinic', function () {
    $city = seedPublicCity();
    $clinic = Clinic::create(['slug' => 'rv-inactive', 'name' => ['en' => 'Inactive'], 'city_id' => $city->id, 'verification_tier' => 'pending', 'is_active' => false]);

    $this->get(route('reviews.show', $clinic->slug))->assertNotFound();
});
