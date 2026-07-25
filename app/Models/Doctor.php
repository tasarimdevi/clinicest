<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

/**
 * See docs/05-database-schema-erd.md §4. `clinic_is_active` is denormalized
 * onto the search document (not just a DB join) so the "at active clinics
 * only" rule below can be expressed as a Meilisearch filter too.
 */
class Doctor extends Model
{
    use HasFactory, HasTranslations, Searchable, SoftDeletes;

    protected $fillable = [
        'public_id', 'slug', 'clinic_id', 'user_id', 'full_name', 'title',
        'specialty', 'bio', 'years_experience', 'languages_json', 'photo_path',
        'rating_avg', 'rating_count', 'is_featured',
    ];

    public array $translatable = ['title', 'specialty', 'bio'];

    protected static function booted(): void
    {
        static::creating(function (self $doctor) {
            $doctor->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'languages_json' => 'array',
            'rating_avg' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DoctorMedia::class);
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
        return 'doctors';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'title' => trim(implode(' ', array_filter($this->getTranslations('title')))),
            'specialty' => trim(implode(' ', array_filter($this->getTranslations('specialty')))),
            'bio' => trim(implode(' ', array_filter($this->getTranslations('bio')))),
            'clinic_name' => $this->clinic ? trim(implode(' ', array_filter($this->clinic->getTranslations('name')))) : null,
            'clinic_id' => $this->clinic_id,
            'languages' => $this->languages_json ?? [],
            'rating_avg' => (float) $this->rating_avg,
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('clinic');
    }

    /**
     * A doctor at an inactive clinic is excluded from the index entirely
     * (rather than filtered per-query) — see Clinic::booted() for the
     * observer that keeps this in sync when a clinic is (de)activated.
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->clinic?->is_active;
    }
}
