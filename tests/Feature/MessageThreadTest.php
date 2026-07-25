<?php

declare(strict_types=1);

use App\Livewire\Admin\LeadDetail;
use App\Livewire\Clinic\MessageThread;
use App\Mail\LeadMessageMail;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedMessageClinic(?string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'M'.$iso2, 'name' => 'Msgland '.$n, 'slug' => 'msgland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'msg-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'msg-clinic-'.uniqid(), 'name' => ['en' => 'Message Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);

    if ($spatieRole) {
        $user->assignRole($spatieRole);
    }

    return [$clinic, $user];
}

function seedAcceptedLeadForMessage(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Message Patient', 'email' => 'message-patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('lets a clinic owner send a reply that actually emails the lead', function () {
    Mail::fake();

    [$clinic, $owner] = seedMessageClinic();
    $lead = seedAcceptedLeadForMessage($clinic);

    Livewire::actingAs($owner)
        ->test(MessageThread::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('reply_body', 'Your treatment plan is ready.')
        ->call('sendReply')
        ->assertHasNoErrors();

    $message = Message::first();
    expect($message)->not->toBeNull();
    expect($message->direction)->toBe('outbound');
    expect($message->channel)->toBe('web');
    expect($message->body)->toBe('Your treatment plan is ready.');

    Mail::assertSent(LeadMessageMail::class, fn ($mail) => $mail->hasTo('message-patient@example.com'));

    expect($lead->activities()->where('type', 'email')->exists())->toBeTrue();
});

it('lets clinic staff log a message from another channel without sending an email', function () {
    Mail::fake();

    [$clinic, $owner] = seedMessageClinic('clinic_staff');
    $lead = seedAcceptedLeadForMessage($clinic);

    Livewire::actingAs($owner)
        ->test(MessageThread::class, ['clinic' => $clinic, 'lead' => $lead])
        ->set('log_channel', 'whatsapp')
        ->set('log_direction', 'inbound')
        ->set('log_body', 'Patient asked about financing options.')
        ->call('logMessage')
        ->assertHasNoErrors();

    $message = Message::first();
    expect($message->direction)->toBe('inbound');
    expect($message->channel)->toBe('whatsapp');

    Mail::assertNothingSent();

    expect($lead->activities()->where('type', 'whatsapp')->exists())->toBeTrue();
});

it('blocks a clinic member without messages.manage from reaching the thread', function () {
    [$clinic, $staffWithoutPermission] = seedMessageClinic(spatieRole: null);
    $lead = seedAcceptedLeadForMessage($clinic);

    $this->actingAs($staffWithoutPermission)
        ->get(route('clinic.messages.index', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('blocks messaging for a lead the clinic has not accepted yet', function () {
    [$clinic, $owner] = seedMessageClinic();
    $lead = Lead::create(['full_name' => 'Unaccepted', 'email' => 'unaccepted@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);

    $this->actingAs($owner)
        ->get(route('clinic.messages.index', ['clinic' => $clinic, 'lead' => $lead]))
        ->assertForbidden();
});

it('scopes the clinic message thread to that clinic only', function () {
    [$clinicA, $ownerA] = seedMessageClinic();
    [$clinicB] = seedMessageClinic();
    $lead = seedAcceptedLeadForMessage($clinicA);
    $lead->assignments()->create(['clinic_id' => $clinicB->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    Message::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinicA->id, 'direction' => 'outbound', 'channel' => 'web',
        'body' => 'From clinic A', 'created_at' => now(),
    ]);
    Message::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinicB->id, 'direction' => 'outbound', 'channel' => 'web',
        'body' => 'From clinic B', 'created_at' => now(),
    ]);

    Livewire::actingAs($ownerA)
        ->test(MessageThread::class, ['clinic' => $clinicA, 'lead' => $lead])
        ->assertSee('From clinic A')
        ->assertDontSee('From clinic B');
});

it('shows every clinic thread on the admin lead detail page', function () {
    [$clinicA] = seedMessageClinic();
    [$clinicB] = seedMessageClinic();
    $lead = seedAcceptedLeadForMessage($clinicA);
    $lead->assignments()->create(['clinic_id' => $clinicB->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    Message::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinicA->id, 'direction' => 'outbound', 'channel' => 'web',
        'body' => 'From clinic A', 'created_at' => now(),
    ]);
    Message::create([
        'lead_id' => $lead->id, 'clinic_id' => $clinicB->id, 'direction' => 'outbound', 'channel' => 'web',
        'body' => 'From clinic B', 'created_at' => now(),
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(LeadDetail::class, ['lead' => $lead])
        ->assertSee('From clinic A')
        ->assertSee('From clinic B');
});
