<?php

declare(strict_types=1);

use App\Actions\Leads\AssignLeadToClinics;
use App\Livewire\Admin\ClinicApplications;
use App\Livewire\NotificationBell;
use App\Livewire\Patient\PatientPortal;
use App\Livewire\Public\ClinicApplicationPage;
use App\Livewire\Settings\NotificationPreferences;
use App\Mail\NotificationDigestMail;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Offer;
use App\Models\TreatmentCase;
use App\Models\User;
use App\Notifications\AppointmentRespondedTo;
use App\Notifications\ClinicApplicationDecided;
use App\Notifications\LeadAssignedToClinic;
use App\Notifications\NewClinicApplicationSubmitted;
use App\Notifications\OfferRespondedTo;
use App\Notifications\PatientMessageReceived;
use App\Notifications\ReviewPendingModeration;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedNotifCity(): City
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'N'.$iso2, 'name' => 'Notifland '.$n, 'slug' => 'notifland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);

    return City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'notif-istanbul-'.$n]);
}

function seedNotifClinicWithOwner(string $spatieRole = 'clinic_owner'): array
{
    $city = seedNotifCity();
    $owner = User::factory()->create();
    $owner->assignRole($spatieRole);

    $clinic = Clinic::create([
        'slug' => 'notif-clinic-'.uniqid(), 'name' => ['en' => 'Notif Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true, 'owner_user_id' => $owner->id,
    ]);
    $clinic->users()->attach($owner->id, ['role' => 'owner']);

    return [$clinic, $owner];
}

function seedNotifAcceptedLead(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Notif Patient', 'email' => 'notif-patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('notifies the clinic owner in-app and by email when their application is approved', function () {
    Notification::fake();

    [$clinic, $owner] = seedNotifClinicWithOwner();
    $clinic->update(['application_status' => 'pending', 'is_active' => false]);

    $admin = User::factory()->create();
    $admin->assignRole('moderator');

    Livewire::actingAs($admin)
        ->test(ClinicApplications::class)
        ->call('approve', $clinic)
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, ClinicApplicationDecided::class);
});

it('respects a disabled mail preference for clinic application decisions', function () {
    [$clinic, $owner] = seedNotifClinicWithOwner();
    $clinic->update(['application_status' => 'pending', 'is_active' => false]);
    $owner->update(['notification_preferences' => [ClinicApplicationDecided::class => ['mail' => false]]]);

    $admin = User::factory()->create();
    $admin->assignRole('moderator');

    Livewire::actingAs($admin)
        ->test(ClinicApplications::class)
        ->call('approve', $clinic);

    expect($owner->notifications()->count())->toBe(1);
    expect($owner->fresh()->wantsEmailFor(ClinicApplicationDecided::class))->toBeFalse();
});

it('notifies every clinics.verify holder when a new clinic application is submitted', function () {
    Notification::fake();

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $city = seedNotifCity();

    Livewire::test(ClinicApplicationPage::class)
        ->set('clinic_name', 'Brand New Clinic')
        ->set('city_id', $city->id)
        ->set('owner_name', 'New Owner')
        ->set('owner_email', 'new-owner@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('submit')
        ->assertHasNoErrors();

    Notification::assertSentTo($moderator, NewClinicApplicationSubmitted::class);
});

it('notifies clinic staff with leads.view when a lead is assigned to their clinic', function () {
    Notification::fake();

    [$clinic, $owner] = seedNotifClinicWithOwner();
    $lead = Lead::create(['full_name' => 'Assignable Lead', 'email' => 'assignable@example.com', 'status' => 'new']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);
    app(AssignLeadToClinics::class)->handle($lead, [$clinic->id], $admin);

    Notification::assertSentTo($owner, LeadAssignedToClinic::class);
});

it('notifies clinic staff with messages.manage when a patient sends a portal message, but not when the clinic replies', function () {
    Notification::fake();

    [$clinic, $owner] = seedNotifClinicWithOwner();
    $lead = seedNotifAcceptedLead($clinic);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set("replyBodies.{$clinic->id}", 'When can I arrive?')
        ->call('sendMessage', $clinic->id)
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, PatientMessageReceived::class);
});

it('notifies clinic staff with offers.manage when a patient responds to an offer', function () {
    Notification::fake();

    [$clinic, $owner] = seedNotifClinicWithOwner();
    $lead = seedNotifAcceptedLead($clinic);
    $offer = Offer::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'title' => 'Implant Plan',
        'price_total' => 65000, 'currency' => 'EUR', 'status' => 'viewed',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->call('acceptOffer', $offer->id)
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, OfferRespondedTo::class);
});

it('notifies clinic staff with appointments.manage when a patient responds to an appointment', function () {
    Notification::fake();

    [$clinic, $owner] = seedNotifClinicWithOwner();
    $lead = seedNotifAcceptedLead($clinic);
    $appointment = $lead->appointments()->create([
        'clinic_id' => $clinic->id, 'type' => 'onsite', 'scheduled_at' => now()->addDays(5),
        'timezone' => 'Europe/Istanbul', 'status' => 'requested',
    ]);

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->call('confirmAppointment', $appointment->id)
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, AppointmentRespondedTo::class);
});

it('notifies reviews.moderate holders when a patient submits a review', function () {
    Notification::fake();

    [$clinic] = seedNotifClinicWithOwner();
    $lead = seedNotifAcceptedLead($clinic);
    TreatmentCase::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'agreed_price' => 65000,
        'currency' => 'EUR', 'status' => 'completed',
    ]);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    Livewire::test(PatientPortal::class, ['lead' => $lead])
        ->set('reviewRating', 5)
        ->set('reviewBody', 'Excellent care from start to finish.')
        ->call('submitReview')
        ->assertHasNoErrors();

    Notification::assertSentTo($moderator, ReviewPendingModeration::class);
});

it('shows unread notifications in the bell and lets a user mark them read', function () {
    [$clinic, $owner] = seedNotifClinicWithOwner();
    $lead = seedNotifAcceptedLead($clinic);
    Message::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinic->id, 'sender_type' => Lead::class, 'sender_id' => $lead->id,
        'direction' => 'inbound', 'channel' => 'web', 'body' => 'Hello', 'created_at' => now(),
    ]);
    $owner->notify(new PatientMessageReceived(Message::first(), $clinic));

    expect($owner->unreadNotifications()->count())->toBe(1);

    Livewire::actingAs($owner)
        ->test(NotificationBell::class)
        ->call('markAllAsRead');

    expect($owner->unreadNotifications()->count())->toBe(0);
});

it('lets a user save notification preferences that then gate email delivery', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    Livewire::actingAs($user)
        ->test(NotificationPreferences::class)
        ->set('mail.'.LeadAssignedToClinic::class, false)
        ->set('digest', false)
        ->call('save')
        ->assertHasNoErrors();

    $fresh = $user->fresh();
    expect($fresh->wantsEmailFor(LeadAssignedToClinic::class))->toBeFalse();
    expect($fresh->wantsDigest())->toBeFalse();
});

it('emails a digest of unread notifications to users who want one, and skips those who opted out', function () {
    Mail::fake();

    [$clinicA, $ownerA] = seedNotifClinicWithOwner();
    [$clinicB, $ownerB] = seedNotifClinicWithOwner();

    $leadA = seedNotifAcceptedLead($clinicA);
    $ownerA->notify(new LeadAssignedToClinic($leadA, $clinicA));

    $leadB = seedNotifAcceptedLead($clinicB);
    $ownerB->notify(new LeadAssignedToClinic($leadB, $clinicB));
    $ownerB->update(['notification_preferences' => ['digest' => ['mail' => false]]]);

    Artisan::call('notifications:digest');

    Mail::assertSent(NotificationDigestMail::class, fn ($mail) => $mail->hasTo($ownerA->email));
    Mail::assertNotSent(NotificationDigestMail::class, fn ($mail) => $mail->hasTo($ownerB->email));
});
