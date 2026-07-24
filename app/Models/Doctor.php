<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Doctor extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

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
}
