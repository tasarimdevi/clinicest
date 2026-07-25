<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Clinic extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'public_id', 'slug', 'name', 'legal_name', 'city_id', 'address',
        'lat', 'lng', 'phone', 'whatsapp', 'email', 'website', 'about',
        'founded_year', 'patients_treated', 'verification_tier', 'verified_at',
        'verified_by', 'response_time_minutes', 'languages_json',
        'rating_avg', 'rating_count', 'is_active', 'is_featured', 'owner_user_id',
    ];

    public array $translatable = ['name', 'about'];

    protected static function booted(): void
    {
        static::creating(function (self $clinic) {
            $clinic->public_id ??= (string) Str::uuid();
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

    public function beforeAfterCases(): HasMany
    {
        return $this->hasMany(BeforeAfterCase::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
