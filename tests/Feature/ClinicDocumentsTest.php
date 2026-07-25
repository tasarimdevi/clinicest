<?php

declare(strict_types=1);

use App\Livewire\Clinic\ClinicDocuments;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Document;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

function seedDocumentClinic(?string $spatieRole = 'clinic_owner'): array
{
    static $n = 0;
    $n++;

    $iso2 = chr(65 + intdiv($n, 26)).chr(65 + ($n % 26));
    $country = Country::create([
        'iso2' => $iso2, 'iso3' => 'D'.$iso2, 'name' => 'Docland '.$n, 'slug' => 'docland-'.$n,
        'currency' => 'TRY', 'is_target' => false,
    ]);
    $city = City::create(['country_id' => $country->id, 'name' => 'Istanbul', 'slug' => 'doc-istanbul-'.$n]);
    $clinic = Clinic::create([
        'slug' => 'doc-clinic-'.uniqid(), 'name' => ['en' => 'Document Clinic'], 'city_id' => $city->id,
        'verification_tier' => 'verified', 'is_active' => true,
    ]);

    $user = User::factory()->create();
    $clinic->users()->attach($user, ['role' => 'owner']);

    if ($spatieRole) {
        $user->assignRole($spatieRole);
    }

    return [$clinic, $user];
}

function seedAcceptedLeadForDocument(Clinic $clinic): Lead
{
    $lead = Lead::create(['full_name' => 'Doc Patient', 'email' => 'doc-patient@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'accepted', 'assigned_at' => now(), 'responded_at' => now()]);

    return $lead;
}

it('lets a clinic owner upload a clinic-level document', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('iso-certificate.pdf', 500, 'application/pdf');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'certificate')
        ->set('title', 'ISO Certificate')
        ->set('file', $file)
        ->call('upload')
        ->assertHasNoErrors();

    $document = Document::first();
    expect($document)->not->toBeNull();
    expect($document->clinic_id)->toBe($clinic->id);
    expect($document->lead_id)->toBeNull();
    expect($document->type->value)->toBe('certificate');

    Storage::disk('local')->assertExists($document->file_path);
});

it('lets a clinic owner upload a document tied to an accepted lead and logs it', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $lead = seedAcceptedLeadForDocument($clinic);
    $file = UploadedFile::fake()->create('treatment-plan.pdf', 200, 'application/pdf');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'treatment_plan')
        ->set('title', 'Treatment Plan')
        ->set('lead_id', $lead->id)
        ->set('file', $file)
        ->call('upload')
        ->assertHasNoErrors();

    $document = Document::first();
    expect($document->lead_id)->toBe($lead->id);
    expect($lead->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('blocks uploading a document tied to a lead the clinic has not accepted', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $lead = Lead::create(['full_name' => 'Unaccepted', 'email' => 'unaccepted-doc@example.com', 'status' => 'assigned']);
    $lead->assignments()->create(['clinic_id' => $clinic->id, 'status' => 'offered', 'assigned_at' => now()]);
    $file = UploadedFile::fake()->create('plan.pdf', 200, 'application/pdf');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'treatment_plan')
        ->set('title', 'Plan')
        ->set('lead_id', $lead->id)
        ->set('file', $file)
        ->call('upload')
        ->assertForbidden();

    expect(Document::count())->toBe(0);
});

it('blocks a clinic member without documents.manage from uploading', function () {
    Storage::fake('local');

    // documents.view but not documents.manage — enough to reach the page
    // (mount() gates on viewAny) but not enough to upload. A user with
    // neither permission never gets past mount(), which — per the
    // established pattern for mount()-time Livewire authorization
    // failures — doesn't propagate cleanly through Livewire::test() and
    // needs a real HTTP request instead; that's covered by the
    // not-accepted-lead / cross-clinic tests already exercising mount().
    [$clinic, $viewOnlyUser] = seedDocumentClinic(spatieRole: null);
    $viewOnlyUser->givePermissionTo('documents.view');

    $file = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    Livewire::actingAs($viewOnlyUser)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'certificate')
        ->set('title', 'Doc')
        ->set('file', $file)
        ->call('upload')
        ->assertForbidden();

    expect(Document::count())->toBe(0);
});

it('blocks a clinic member without documents.view from reaching the documents page', function () {
    [$clinic, $userWithoutAnyPermission] = seedDocumentClinic(spatieRole: null);

    $this->actingAs($userWithoutAnyPermission)
        ->get(route('clinic.documents.index', ['clinic' => $clinic]))
        ->assertForbidden();
});

it('rejects a disallowed file type', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('script.exe', 200, 'application/octet-stream');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'certificate')
        ->set('title', 'Doc')
        ->set('file', $file)
        ->call('upload')
        ->assertHasErrors(['file']);
});

it('lets a clinic owner delete their own document and removes the file', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'certificate')
        ->set('title', 'Doc')
        ->set('file', $file)
        ->call('upload');

    $document = Document::first();

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->call('delete', $document->id)
        ->assertHasNoErrors();

    expect(Document::count())->toBe(0);
    Storage::disk('local')->assertMissing($document->file_path);
});

it('blocks deleting a document belonging to another clinic', function () {
    Storage::fake('local');

    [$clinicA, $ownerA] = seedDocumentClinic();
    [$clinicB, $ownerB] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    Livewire::actingAs($ownerA)
        ->test(ClinicDocuments::class, ['clinic' => $clinicA])
        ->set('type', 'certificate')
        ->set('title', 'Doc A')
        ->set('file', $file)
        ->call('upload');

    $document = Document::first();

    Livewire::actingAs($ownerB)
        ->test(ClinicDocuments::class, ['clinic' => $clinicB])
        ->call('delete', $document->id)
        ->assertForbidden();

    expect(Document::count())->toBe(1);
});

it('lets the owning clinic download its document but blocks an unrelated clinic member', function () {
    Storage::fake('local');

    [$clinicA, $ownerA] = seedDocumentClinic();
    [$clinicB, $ownerB] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    Livewire::actingAs($ownerA)
        ->test(ClinicDocuments::class, ['clinic' => $clinicA])
        ->set('type', 'certificate')
        ->set('title', 'Doc A')
        ->set('file', $file)
        ->call('upload');

    $document = Document::first();

    $this->actingAs($ownerA)->get(route('documents.download', $document))->assertOk();
    $this->actingAs($ownerB)->get(route('documents.download', $document))->assertForbidden();
});

it('lets admin download any document via access-admin', function () {
    Storage::fake('local');

    [$clinic, $owner] = seedDocumentClinic();
    $file = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    Livewire::actingAs($owner)
        ->test(ClinicDocuments::class, ['clinic' => $clinic])
        ->set('type', 'certificate')
        ->set('title', 'Doc')
        ->set('file', $file)
        ->call('upload');

    $document = Document::first();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('documents.download', $document))->assertOk();
});
