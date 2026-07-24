<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\VerificationTier;
use App\Models\City;
use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Handles both create and edit — see docs/09-crm-admin-architecture.md §4.
 * The verification-tier control is authorized separately from the rest of
 * the form (clinics.verify vs clinics.manage) so a moderator who reaches
 * this page purely to verify a clinic can't also edit its profile.
 */
#[Layout('layouts.app', ['title' => 'Clinic'])]
class ClinicForm extends Component
{
    public ?Clinic $clinic = null;

    public string $name = '';

    public string $slug = '';

    public ?int $city_id = null;

    public string $address = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $email = '';

    public string $website = '';

    public string $about = '';

    public ?int $founded_year = null;

    /** @var array<int, string> */
    public array $languages = [];

    public string $verification_tier = 'pending';

    public bool $is_active = false;

    public bool $is_featured = false;

    /**
     * $clinic is deliberately NOT type-hinted as Clinic here. Laravel's
     * container resolves a type-hinted `?Clinic $clinic = null` parameter
     * by auto-instantiating a blank `new Clinic()` whenever no matching
     * route/test parameter is supplied (since Eloquent models are
     * concrete, zero-arg-constructible classes) — it never falls through
     * to the `null` default. That silently breaks the create route
     * (`/admin/clinics/create`, no {clinic} segment) by handing mount() a
     * non-null-but-empty model. Accepting the raw value and resolving it
     * manually avoids that container gotcha entirely.
     */
    public function mount(mixed $clinic = null): void
    {
        $model = match (true) {
            $clinic instanceof Clinic => $clinic,
            $clinic !== null => Clinic::findOrFail($clinic),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Clinic::class);

        if ($model) {
            $this->clinic = $model;
            $this->name = $model->getTranslation('name', 'en') ?? '';
            $this->slug = $model->slug;
            $this->city_id = $model->city_id;
            $this->address = $model->address ?? '';
            $this->phone = $model->phone ?? '';
            $this->whatsapp = $model->whatsapp ?? '';
            $this->email = $model->email ?? '';
            $this->website = $model->website ?? '';
            $this->about = $model->getTranslation('about', 'en') ?? '';
            $this->founded_year = $model->founded_year;
            $this->languages = $model->languages_json ?? [];
            $this->verification_tier = $model->verification_tier->value;
            // Eloquent's create() doesn't reload DB-level column defaults
            // into the in-memory model, so a clinic inserted without an
            // explicit is_active/is_featured can have a null attribute
            // here even though the column defaults to false — cast it.
            $this->is_active = (bool) $model->is_active;
            $this->is_featured = (bool) $model->is_featured;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('clinics', 'slug')->ignore($this->clinic?->id)],
            'city_id' => ['required', Rule::exists(City::class, 'id')],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->clinic ? 'manage' : 'create', $this->clinic ?? Clinic::class);

        $validated = $this->validate();
        $validated['languages_json'] = $this->languages;
        $validated['is_active'] = $this->is_active;
        $validated['is_featured'] = $this->is_featured;

        if ($this->clinic) {
            $this->clinic->update($validated);
        } else {
            $this->clinic = Clinic::create($validated);
        }

        $this->redirect(route('admin.clinics.edit', $this->clinic), navigate: false);
    }

    public function updateVerificationTier(string $tier): void
    {
        $this->authorize('verify', $this->clinic);

        $this->clinic->update([
            'verification_tier' => VerificationTier::from($tier),
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        $this->verification_tier = $tier;
        $this->clinic->refresh();
    }

    public function render(): View
    {
        return view('livewire.admin.clinic-form', [
            'cities' => City::orderBy('name')->get(),
            'tiers' => VerificationTier::cases(),
        ]);
    }
}
