<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithTranslations;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Services\ImageProcessor;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * `title`/`specialty`/`bio` are Spatie-translatable and edited per-locale
 * (EN/TR) via WithTranslations + <x-translatable-field>; `full_name` is a
 * plain column (a person's name isn't translated).
 */
#[Layout('layouts.app', ['title' => 'Doctor'])]
class DoctorForm extends Component
{
    use WithFileUploads, WithTranslations;

    public ?Doctor $doctor = null;

    public $photo = null;

    public string $full_name = '';

    public string $slug = '';

    public ?int $clinic_id = null;

    /** @var array<string, string> */
    public array $title = [];

    /** @var array<string, string> */
    public array $specialty = [];

    /** @var array<string, string> */
    public array $bio = [];

    public ?int $years_experience = null;

    /** @var array<int, string> */
    public array $languages = [];

    public bool $is_featured = false;

    /**
     * $doctor is deliberately not type-hinted as Doctor — see the identical
     * note on App\Livewire\Admin\ClinicForm::mount(). A typed nullable
     * Eloquent parameter gets auto-instantiated by the container instead
     * of staying null when there's no route/test value for it (e.g. the
     * create route, with no {doctor} segment), silently breaking creation.
     */
    protected function translatableFields(): array
    {
        return ['title' => 'title', 'specialty' => 'specialty', 'bio' => 'bio'];
    }

    public function mount(mixed $doctor = null): void
    {
        $model = match (true) {
            $doctor instanceof Doctor => $doctor,
            $doctor !== null => Doctor::findOrFail($doctor),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Doctor::class);

        $this->emptyTranslations(['title', 'specialty', 'bio']);

        if ($model) {
            $this->doctor = $model;
            $this->fillTranslations($model);
            $this->full_name = $model->full_name;
            $this->slug = $model->slug;
            $this->clinic_id = $model->clinic_id;
            $this->years_experience = $model->years_experience;
            $this->languages = $model->languages_json ?? [];
            $this->is_featured = (bool) $model->is_featured;
        }
    }

    protected function rules(): array
    {
        return [
            ...$this->translationRules([
                'title' => ['max' => 255],
                'specialty' => ['max' => 255],
                'bio' => ['max' => 2000],
            ]),
            'full_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('doctors', 'slug')->ignore($this->doctor?->id)],
            'clinic_id' => ['required', Rule::exists(Clinic::class, 'id')],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function save(ImageProcessor $processor): void
    {
        $this->authorize($this->doctor ? 'update' : 'create', $this->doctor ?? Doctor::class);

        $validated = $this->validate();

        $model = $this->doctor ?? new Doctor;
        $model->fill([
            'full_name' => $validated['full_name'],
            'slug' => $validated['slug'],
            'clinic_id' => $validated['clinic_id'],
            'years_experience' => $validated['years_experience'] ?? null,
            'languages_json' => $this->languages,
            'is_featured' => $this->is_featured,
        ]);
        $this->applyTranslations($model);
        $model->save();
        $this->doctor = $model;

        // Stored after the create/update so a new doctor already has an id
        // for the path. Downscaled+compressed (no WebP variant — avatars
        // render tiny, compression is enough). Replacing a photo deletes
        // the old file rather than orphaning it on the public disk.
        if ($this->photo) {
            $old = $this->doctor->photo_path;
            $optimized = $processor->storeOptimized($this->photo, "doctor-photos/{$this->doctor->id}", 'public');
            $this->doctor->update(['photo_path' => $optimized['path']]);

            if ($old) {
                $processor->delete($old);
            }

            $this->reset('photo');
        }

        $this->redirect(route('admin.doctors.edit', $this->doctor), navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.doctor-form', [
            'clinics' => Clinic::orderBy('slug')->get(),
        ]);
    }
}
