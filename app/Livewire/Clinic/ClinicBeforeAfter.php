<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Media\UploadBeforeAfterCase;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Clinic-side before/after submission. Gated on clinics.manage (same as
 * the profile editor / gallery) — the clinic owns the patient relationship
 * and attests to consent. Every submission is unpublished until a
 * moderator approves it (BeforeAfterModeration), so nothing a clinic
 * uploads is trusted straight onto public pages.
 */
#[Layout('layouts.app', ['title' => 'Before / After'])]
class ClinicBeforeAfter extends Component
{
    use WithFileUploads;

    public Clinic $clinic;

    public ?int $treatment_id = null;

    public ?int $doctor_id = null;

    public ?int $patient_country_id = null;

    public string $title = '';

    public string $description = '';

    public $before = null;

    public $after = null;

    public bool $consent = false;

    public function mount(Clinic $clinic): void
    {
        $this->authorize('manage', $clinic);

        $this->clinic = $clinic;
    }

    protected function rules(): array
    {
        return [
            'treatment_id' => ['required', Rule::exists('treatments', 'id')],
            'doctor_id' => ['nullable', Rule::exists('doctors', 'id')],
            'patient_country_id' => ['nullable', Rule::exists('countries', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'before' => ['required', 'image', 'max:5120'],
            'after' => ['required', 'image', 'max:5120'],
            'consent' => ['accepted'],
        ];
    }

    public function submit(UploadBeforeAfterCase $uploadBeforeAfterCase): void
    {
        $this->authorize('manage', $this->clinic);

        $validated = $this->validate();

        // A doctor picked here must actually belong to this clinic —
        // the <select> is already scoped, but never trust the client id.
        if ($validated['doctor_id']) {
            abort_unless($this->clinic->doctors()->whereKey($validated['doctor_id'])->exists(), 403);
        }

        $uploadBeforeAfterCase->handle($this->clinic, $this->before, $this->after, [
            'treatment_id' => $validated['treatment_id'],
            'doctor_id' => $validated['doctor_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'patient_country_id' => $validated['patient_country_id'],
        ], auth()->user());

        $this->reset(['treatment_id', 'doctor_id', 'patient_country_id', 'title', 'description', 'before', 'after', 'consent']);
    }

    public function delete(int $caseId): void
    {
        $this->authorize('manage', $this->clinic);

        $case = $this->clinic->beforeAfterCases()->findOrFail($caseId);

        Storage::disk('public')->delete(array_filter([$case->before_media_path, $case->after_media_path]));
        $case->delete();
    }

    public function render(): View
    {
        return view('livewire.clinic.clinic-before-after', [
            'cases' => $this->clinic->beforeAfterCases()->with('treatment')->latest()->get(),
            'treatments' => Treatment::where('status', 'published')->orderBy('sort')->get(),
            'doctors' => $this->clinic->doctors()->orderBy('full_name')->get(),
            'countries' => Country::orderBy('name')->get(),
        ]);
    }
}
