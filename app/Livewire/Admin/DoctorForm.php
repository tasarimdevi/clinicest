<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Doctor'])]
class DoctorForm extends Component
{
    public ?Doctor $doctor = null;

    public string $full_name = '';

    public string $slug = '';

    public ?int $clinic_id = null;

    public string $title = '';

    public string $specialty = '';

    public string $bio = '';

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
    public function mount(mixed $doctor = null): void
    {
        $model = match (true) {
            $doctor instanceof Doctor => $doctor,
            $doctor !== null => Doctor::findOrFail($doctor),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Doctor::class);

        if ($model) {
            $this->doctor = $model;
            $this->full_name = $model->full_name;
            $this->slug = $model->slug;
            $this->clinic_id = $model->clinic_id;
            $this->title = $model->getTranslation('title', 'en') ?? '';
            $this->specialty = $model->getTranslation('specialty', 'en') ?? '';
            $this->bio = $model->getTranslation('bio', 'en') ?? '';
            $this->years_experience = $model->years_experience;
            $this->languages = $model->languages_json ?? [];
            $this->is_featured = (bool) $model->is_featured;
        }
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('doctors', 'slug')->ignore($this->doctor?->id)],
            'clinic_id' => ['required', Rule::exists(Clinic::class, 'id')],
            'title' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->doctor ? 'update' : 'create', $this->doctor ?? Doctor::class);

        $validated = $this->validate();
        $validated['languages_json'] = $this->languages;
        $validated['is_featured'] = $this->is_featured;

        if ($this->doctor) {
            $this->doctor->update($validated);
        } else {
            $this->doctor = Doctor::create($validated);
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
