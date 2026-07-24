<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Treatment extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

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
}
