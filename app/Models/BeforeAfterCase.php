<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class BeforeAfterCase extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'public_id', 'clinic_id', 'doctor_id', 'treatment_id', 'title', 'description',
        'before_media_path', 'after_media_path', 'patient_country_id',
        'consent_confirmed', 'is_published',
    ];

    public array $translatable = ['title', 'description'];

    protected static function booted(): void
    {
        static::creating(function (self $case) {
            $case->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'consent_confirmed' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function patientCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'patient_country_id');
    }

    public function hasPhotos(): bool
    {
        return $this->before_media_path !== null && $this->after_media_path !== null;
    }

    /**
     * Public-disk URLs. A path may be a bare storage path (clinic upload)
     * or already an absolute URL (legacy/seed data) — an already-absolute
     * value is returned as-is so both keep working.
     */
    protected function beforeUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->resolveMediaUrl($this->before_media_path));
    }

    protected function afterUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->resolveMediaUrl($this->after_media_path));
    }

    protected function resolveMediaUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset('storage/'.$path);
    }
}
