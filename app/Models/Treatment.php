<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

/**
 * See docs/05-database-schema-erd.md §4. `status` is indexed (not just
 * filtered in Eloquent) so a draft treatment can never surface through a
 * Meilisearch-backed search either.
 */
class Treatment extends Model
{
    use HasFactory, HasTranslations, Searchable, SoftDeletes;

    protected $fillable = [
        'slug', 'name', 'summary', 'body', 'category_id', 'icon',
        'avg_duration_min', 'recovery_days', 'trips_required',
        'base_price_min', 'base_price_max', 'currency',
        'is_featured', 'sort', 'status',
    ];

    public array $translatable = ['name', 'summary', 'body'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TreatmentCategory::class, 'category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TreatmentCategory::class, 'treatment_category_map');
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_treatment')
            ->withPivot(['price_min', 'price_max', 'currency', 'notes', 'is_available'])
            ->withTimestamps();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'primary_treatment_id');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    public function beforeAfterCases(): HasMany
    {
        return $this->hasMany(BeforeAfterCase::class);
    }

    public function countryTreatments(): HasMany
    {
        return $this->hasMany(CountryTreatment::class);
    }

    public function searchableAs(): string
    {
        return 'treatments';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => trim(implode(' ', array_filter($this->getTranslations('name')))),
            'summary' => trim(implode(' ', array_filter($this->getTranslations('summary')))),
            'category_id' => $this->category_id,
            'category_name' => $this->category ? trim(implode(' ', array_filter($this->category->getTranslations('name')))) : null,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('category');
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }
}
