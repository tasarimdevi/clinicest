<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Enums\OfferStatus;
use App\Livewire\Patient\PatientPortal;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Review;
use App\Models\TreatmentCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function seedPortalClinic(): Clinic
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'Q'.$iso2, 'name' => 'Portalland '.$n, 'slug' => 'portalland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'portal-istanbul-'.$n]);

    return Clinic::create([
        'slug' => 'portal-clinic-'.uniqid(), 'name' => ['en' => 'Portal Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);
}

function seedPortalLead(Clinic $clinic, string $assignmentStatus = 'accepted'): Lead
{
    $lead = Lead::create(['full_name' => 'Portal Patient', 'email' => 'portal-patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create([
        'clinic_id' => $clinic->id, 'status' => $assignmentStatus, 'assigned_at' => now(), 'responded_at' => now(),
    ]);

    return $lead;
}

function portalUrl(Lead $lead): string
{
    return URL::temporarySignedRoute('patient.portal.show', now()->addDays(60), ['lead' => $lead->public_id]);
}

it('denies access to the portal without a valid signature', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);

    $this->get(route('patient.portal.show', $lead->public_id))->assertForbidden();
});

it('allows access via a valid signed link and marks sent offers as viewed', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    $offer = Offer::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'Implant Plan',
        'price_total' => 65000, 'currency' => 'EUR', 'status' => 'sent',
    ]);

    $this->get(portalUrl($lead))->assertOk();

    expect($offer->fresh()->status)->toBe(OfferStatus::Viewed);
});

it('lets a patient accept an offer', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    $offer = Offer::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'Implant Plan',
        'price_total' => 65000, 'currency' => 'EUR', 'status' => 'viewed',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->call('acceptOffer', $offer->id)
        ->assertHasNoErrors();

    expect($offer->fresh()->status)->toBe(OfferStatus::Accepted);
    expect($lead->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('refuses to accept an offer that is already accepted', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    $offer = Offer::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'Implant Plan',
        'price_total' => 65000, 'currency' => 'EUR', 'status' => 'accepted',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->call('acceptOffer', $offer->id)
        ->assertForbidden();
});

it('lets a patient confirm and then cancel an appointment', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    $appointment = $lead->appointments()->create([
        'clinic_id' => $clinic->id, 'type' => 'onsite', 'scheduled_at' => now()->addDays(5),
        'timezone' => 'Europe/Istanbul', 'status' => 'requested',
    ]);

    $component = Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->call('confirmAppointment', $appointment->id)
        ->assertHasNoErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);

    $component->call('cancelAppointment', $appointment->id)->assertHasNoErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
});

it('lets a patient message an accepted clinic without emailing themselves', function () {
    Mail::fake();

    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set("replyBodies.{$clinic->id}", 'When can I arrive?')
        ->call('sendMessage', $clinic->id)
        ->assertHasNoErrors();

    $message = Message::first();
    expect($message)->not->toBeNull();
    expect($message->direction)->toBe('inbound');
    expect($message->sender_type)->toBe(Lead::class);
    expect($message->sender_id)->toBe($lead->id);

    Mail::assertNothingSent();
});

it('blocks messaging a clinic the lead has not been accepted by', function () {
    $clinic = seedPortalClinic();
    $otherClinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set("replyBodies.{$otherClinic->id}", 'Hello?')
        ->call('sendMessage', $otherClinic->id)
        ->assertForbidden();
});

it('lets a patient submit a verified review after a completed treatment case', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 65000,
        'currency' => 'EUR', 'status' => 'completed',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set('reviewRating', 5)
        ->set('reviewBody', 'Excellent care from start to finish.')
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSet('reviewSubmitted', true);

    $review = Review::first();
    expect($review)->not->toBeNull();
    expect($review->lead_id)->toBe($lead->id);
    expect($review->is_verified)->toBeTrue();
    expect($review->status)->toBe('pending');
});

it('blocks a review submission when there is no completed treatment case', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set('reviewRating', 5)
        ->set('reviewBody', 'Great!')
        ->call('submitReview')
        ->assertForbidden();

    expect(Review::count())->toBe(0);
});

it('blocks a duplicate review from the same lead for the same clinic', function () {
    $clinic = seedPortalClinic();
    $lead = seedPortalLead($clinic);
    TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 65000,
        'currency' => 'EUR', 'status' => 'completed',
    ]);

    Review::create([
        'reviewable_type' => Clinic::class, 'reviewable_id' => $clinic->id, 'lead_id' => $lead->id,
        'reviewer_name' => $lead->full_name, 'rating' => 5, 'body' => 'First review.',
        'is_verified' => true, 'status' => 'pending',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set('reviewRating', 4)
        ->set('reviewBody', 'Second attempt.')
        ->call('submitReview')
        ->assertForbidden();

    expect(Review::count())->toBe(1);
});
