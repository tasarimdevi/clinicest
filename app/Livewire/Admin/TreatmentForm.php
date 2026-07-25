<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithTranslations;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Create/edit a treatment. Mirrors PostForm (container auto-resolution
 * workaround on mount, translatable fields via WithTranslations, and the
 * update/publish split). Prices are entered in major units and stored as
 * integer minor units, the same convention as ClinicProfile.
 */
#[Layout('layouts.app', ['title' => 'Treatment'])]
class TreatmentForm extends Component
{
    use WithTranslations;

    public ?Treatment $treatment = null;

    /** @var array<string, string> */
    public array $name = [];

    /** @var array<string, string> */
    public array $summary = [];

    /** @var array<string, string> */
    public array $body = [];

    public string $slug = '';

    public ?int $category_id = null;

    public string $icon = '';

    public ?int $avg_duration_min = null;

    public ?int $recovery_days = null;

    public ?int $trips_required = null;

    public string $priceMin = '';

    public string $priceMax = '';

    public string $currency = 'EUR';

    public bool $is_featured = false;

    public int $sort = 0;

    public string $status = 'draft';

    protected function translatableFields(): array
    {
        return ['name' => 'name', 'summary' => 'summary', 'body' => 'body'];
    }

    public function mount(mixed $treatment = null): void
    {
        $model = match (true) {
            $treatment instanceof Treatment => $treatment,
            $treatment !== null => Treatment::findOrFail($treatment),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Treatment::class);

        $this->emptyTranslations(['name', 'summary', 'body']);

        if ($model) {
            $this->treatment = $model;
            $this->fillTranslations($model);
            $this->slug = $model->slug;
            $this->category_id = $model->category_id;
            $this->icon = $model->icon ?? '';
            $this->avg_duration_min = $model->avg_duration_min;
            $this->recovery_days = $model->recovery_days;
            $this->trips_required = $model->trips_required;
            $this->priceMin = $model->base_price_min !== null ? number_format($model->base_price_min / 100, 2, '.', '') : '';
            $this->priceMax = $model->base_price_max !== null ? number_format($model->base_price_max / 100, 2, '.', '') : '';
            // Model::create() doesn't reload DB column defaults (currency
            // 'EUR', status 'draft') into the in-memory model, so a row
            // inserted without them explicitly set is null here — coalesce.
            $this->currency = $model->currency ?? 'EUR';
            $this->is_featured = (bool) $model->is_featured;
            $this->sort = (int) $model->sort;
            $this->status = $model->status ?? 'draft';
        }
    }

    protected function rules(): array
    {
        return [
            ...$this->translationRules([
                'name' => ['required' => true, 'max' => 255],
                'summary' => ['max' => 500],
                'body' => ['max' => null],
            ]),
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('treatments', 'slug')->ignore($this->treatment?->id)],
            'category_id' => ['nullable', Rule::exists(TreatmentCategory::class, 'id')],
            'icon' => ['nullable', 'string', 'max:255'],
            'avg_duration_min' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'recovery_days' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'trips_required' => ['nullable', 'integer', 'min:0', 'max:255'],
            'priceMin' => ['nullable', 'numeric', 'min:0'],
            'priceMax' => ['nullable', 'numeric', 'gte:priceMin'],
            'currency' => ['required', 'string', 'size:3'],
            'sort' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->treatment ? 'update' : 'create', $this->treatment ?? Treatment::class);

        $validated = $this->validate();

        $model = $this->treatment ?? new Treatment;
        $model->fill([
            'slug' => $validated['slug'],
            'category_id' => $validated['category_id'] ?? null,
            'icon' => $validated['icon'] ?: null,
            'avg_duration_min' => $validated['avg_duration_min'],
            'recovery_days' => $validated['recovery_days'],
            'trips_required' => $validated['trips_required'],
            'base_price_min' => $this->priceMin !== '' ? (int) round(((float) $this->priceMin) * 100) : null,
            'base_price_max' => $this->priceMax !== '' ? (int) round(((float) $this->priceMax) * 100) : null,
            'currency' => strtoupper($validated['currency']),
            'is_featured' => $this->is_featured,
            'sort' => $validated['sort'],
        ]);
        $this->applyTranslations($model);

        // New treatments start as drafts; going live is the separate,
        // content.publish-gated toggle (same as posts).
        if (! $this->treatment) {
            $model->status = 'draft';
        }

        $model->save();
        $this->treatment = $model;

        $this->redirect(route('admin.treatments.edit', $this->treatment), navigate: false);
    }

    public function publish(): void
    {
        $this->authorize('publish', $this->treatment);
        $this->treatment->update(['status' => 'published']);
        $this->status = 'published';
    }

    public function unpublish(): void
    {
        $this->authorize('publish', $this->treatment);
        $this->treatment->update(['status' => 'draft']);
        $this->status = 'draft';
    }

    public function render(): View
    {
        return view('livewire.admin.treatment-form', [
            'categories' => TreatmentCategory::orderBy('sort')->get(),
        ]);
    }
}
