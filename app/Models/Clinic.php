<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationTier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

/**
 * See docs/05-database-schema-erd.md §4 — one of the five Meilisearch
 * indexes (clinics, doctors, treatments, posts, cities). Facets:
 * treatment (treatment_ids), city, verification_tier, language, rating.
 */
class Clinic extends Model
{
    use HasFactory, HasTranslations, Searchable, SoftDeletes;

    protected $fillable = [
        'public_id', 'slug', 'name', 'legal_name', 'city_id', 'address',
        'lat', 'lng', 'phone', 'whatsapp', 'email', 'website', 'about',
        'founded_year', 'patients_treated', 'verification_tier', 'verified_at',
        'verified_by', 'response_time_minutes', 'languages_json',
        'rating_avg', 'rating_count', 'is_active', 'is_featured', 'owner_user_id',
        'application_status', 'application_message', 'credentials_url',
        'rejection_reason', 'applied_at',
    ];

    public array $translatable = ['name', 'about'];

    protected static function booted(): void
    {
        static::creating(function (self $clinic) {
            $clinic->public_id ??= (string) Str::uuid();
        });

        // Doctor::shouldBeSearchable() excludes doctors at an inactive
        // clinic from the search index — when a clinic flips is_active,
        // its doctors' search documents would otherwise go stale until
        // each doctor record is independently touched.
        static::updated(function (self $clinic) {
            if ($clinic->wasChanged('is_active')) {
                $clinic->doctors()->searchable();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'verification_tier' => VerificationTier::class,
            'verified_at' => 'datetime',
            'languages_json' => 'array',
            'rating_avg' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_user')
            ->withPivot(['role', 'invited_at'])
            ->withTimestamps();
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ClinicMedia::class);
    }

    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(Treatment::class, 'clinic_treatment')
            ->withPivot(['price_min', 'price_max', 'currency', 'notes', 'is_available'])
            ->withTimestamps();
    }

    public function leadAssignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentCases(): HasMany
    {
        return $this->hasMany(TreatmentCase::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function beforeAfterCases(): HasMany
    {
        return $this->hasMany(BeforeAfterCase::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function searchableAs(): string
    {
        return 'clinics';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => trim(implode(' ', array_filter($this->getTranslations('name')))),
            'about' => trim(implode(' ', array_filter($this->getTranslations('about')))),
            'city' => $this->city?->name,
            'country' => $this->city?->country?->name,
            'treatment_ids' => $this->treatments->pluck('id')->all(),
            'treatment_names' => $this->treatments
                ->flatMap(fn (Treatment $t) => array_filter($t->getTranslations('name')))
                ->values()
                ->all(),
            'city_id' => $this->city_id,
            'verification_tier' => $this->verification_tier?->value,
            'languages' => $this->languages_json ?? [],
            'rating_avg' => (float) $this->rating_avg,
            'is_active' => (bool) $this->is_active,
        ];
    }

    /**
     * Bulk `scout:import` would otherwise N+1 on city/treatments per row.
     */
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['city.country', 'treatments']);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }
}
