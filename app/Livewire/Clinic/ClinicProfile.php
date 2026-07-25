<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Media\UploadClinicMedia;
use App\Models\Clinic;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * See docs/09-crm-admin-architecture.md §3 "profile editor" — the last
 * item from that section's list still outstanding. Gated on the existing
 * `manage` ability (clinics.manage), the same one ClinicForm's save()
 * already uses admin-side; clinic_owner has it, clinic_manager doesn't
 * (docs/09 §1: only Clinic Owner manages the profile, Clinic Manager is
 * leads/offers/messages/appointments/docs only) — this route just gives
 * clinic_owner a way to reach that ability without needing access-admin,
 * which clinic-portal roles never get.
 *
 * Slug and city aren't editable here: slug changes break existing public
 * URLs/backlinks, and relocating a clinic is a rare, significant change —
 * both stay admin-only (via ClinicForm), shown here as read-only
 * reference. Doctor management already has its own admin CRUD and isn't
 * duplicated here — docs/09 lists "profile editor" as its own item,
 * separate from doctors.
 */
#[Layout('layouts.app', ['title' => 'Clinic Profile'])]
class ClinicProfile extends Component
{
    use WithFileUploads;

    public Clinic $clinic;

    public $newMedia = null;

    public string $newMediaCaption = '';

    public string $name = '';

    public string $about = '';

    public string $address = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $email = '';

    public string $website = '';

    public ?int $founded_year = null;

    /** @var array<int, string> */
    public array $languages = [];

    public bool $saved = false;

    public ?int $newTreatmentId = null;

    public string $newPriceMin = '';

    public string $newPriceMax = '';

    public string $newCurrency = 'EUR';

    /** @var array<int, array{min: string, max: string, currency: string}> */
    public array $prices = [];

    public function mount(Clinic $clinic): void
    {
        $this->authorize('manage', $clinic);

        $this->clinic = $clinic;
        $this->name = $clinic->getTranslation('name', 'en') ?? '';
        $this->about = $clinic->getTranslation('about', 'en') ?? '';
        $this->address = $clinic->address ?? '';
        $this->phone = $clinic->phone ?? '';
        $this->whatsapp = $clinic->whatsapp ?? '';
        $this->email = $clinic->email ?? '';
        $this->website = $clinic->website ?? '';
        $this->founded_year = $clinic->founded_year;
        $this->languages = $clinic->languages_json ?? [];

        foreach ($clinic->treatments as $treatment) {
            $this->prices[$treatment->id] = [
                'min' => number_format($treatment->pivot->price_min / 100, 2, '.', ''),
                'max' => number_format($treatment->pivot->price_max / 100, 2, '.', ''),
                'currency' => $treatment->pivot->currency,
            ];
        }
    }

    public function save(): void
    {
        $this->authorize('manage', $this->clinic);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
        ]);
        $validated['languages_json'] = $this->languages;

        $this->clinic->update($validated);

        $this->saved = true;
    }

    public function addTreatment(): void
    {
        $this->authorize('manage', $this->clinic);

        $validated = $this->validate([
            'newTreatmentId' => ['required', Rule::exists('treatments', 'id')],
            'newPriceMin' => ['required', 'numeric', 'min:0'],
            'newPriceMax' => ['required', 'numeric', 'gte:newPriceMin'],
            'newCurrency' => ['required', 'string', 'size:3'],
        ]);

        $this->clinic->treatments()->syncWithoutDetaching([
            $validated['newTreatmentId'] => [
                'price_min' => (int) round($validated['newPriceMin'] * 100),
                'price_max' => (int) round($validated['newPriceMax'] * 100),
                'currency' => strtoupper($validated['newCurrency']),
                'is_available' => true,
            ],
        ]);

        $this->reset(['newTreatmentId', 'newPriceMin', 'newPriceMax']);
    }

    public function updateTreatmentPrice(int $treatmentId): void
    {
        $this->authorize('manage', $this->clinic);

        abort_unless($this->clinic->treatments()->where('treatment_id', $treatmentId)->exists(), 404);

        $validated = $this->validate([
            "prices.$treatmentId.min" => ['required', 'numeric', 'min:0'],
            "prices.$treatmentId.max" => ['required', 'numeric', 'gte:prices.'.$treatmentId.'.min'],
            "prices.$treatmentId.currency" => ['required', 'string', 'size:3'],
        ]);

        $data = $validated['prices'][$treatmentId];

        $this->clinic->treatments()->updateExistingPivot($treatmentId, [
            'price_min' => (int) round($data['min'] * 100),
            'price_max' => (int) round($data['max'] * 100),
            'currency' => strtoupper($data['currency']),
        ]);
    }

    public function toggleTreatmentAvailability(int $treatmentId): void
    {
        $this->authorize('manage', $this->clinic);

        $pivot = $this->clinic->treatments()->where('treatment_id', $treatmentId)->first()?->pivot;

        abort_if($pivot === null, 404);

        $this->clinic->treatments()->updateExistingPivot($treatmentId, ['is_available' => ! $pivot->is_available]);
    }

    public function removeTreatment(int $treatmentId): void
    {
        $this->authorize('manage', $this->clinic);

        $this->clinic->treatments()->detach($treatmentId);

        unset($this->prices[$treatmentId]);
    }

    public function uploadMedia(UploadClinicMedia $uploadClinicMedia): void
    {
        $this->authorize('manage', $this->clinic);

        $this->validate([
            'newMedia' => ['required', 'image', 'max:5120'],
            'newMediaCaption' => ['nullable', 'string', 'max:255'],
        ]);

        $uploadClinicMedia->handle($this->clinic, $this->newMedia, $this->newMediaCaption ?: null);

        $this->reset(['newMedia', 'newMediaCaption']);
    }

    public function setCoverMedia(int $mediaId): void
    {
        $this->authorize('manage', $this->clinic);

        $media = $this->clinic->media()->findOrFail($mediaId);

        $this->clinic->media()->where('id', '!=', $media->id)->update(['is_cover' => false]);
        $media->update(['is_cover' => true]);
    }

    public function deleteMedia(int $mediaId): void
    {
        $this->authorize('manage', $this->clinic);

        $media = $this->clinic->media()->findOrFail($mediaId);
        $wasCover = $media->is_cover;

        Storage::disk('public')->delete($media->path);
        $media->delete();

        // Keep the gallery from ever having zero covers while photos still
        // exist — promote whatever's now first rather than leaving every
        // remaining photo un-highlighted.
        if ($wasCover) {
            $this->clinic->media()->orderBy('sort')->first()?->update(['is_cover' => true]);
        }
    }

    public function render(): View
    {
        $offeredIds = $this->clinic->treatments()->pluck('treatments.id');

        return view('livewire.clinic.clinic-profile', [
            'offeredTreatments' => $this->clinic->treatments()->orderBy('sort')->get(),
            'availableTreatments' => Treatment::where('status', 'published')->whereNotIn('id', $offeredIds)->orderBy('sort')->get(),
            'media' => $this->clinic->media()->orderByDesc('is_cover')->orderBy('sort')->get(),
        ]);
    }
}
