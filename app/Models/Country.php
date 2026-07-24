<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'iso2', 'iso3', 'name', 'slug', 'currency', 'dial_code',
        'flag_path', 'is_target', 'tier',
        'primary_language', 'flight_note', 'avg_flight_hours', 'visa_info', 'best_time_to_visit',
    ];

    protected function casts(): array
    {
        return [
            'is_target' => 'boolean',
            'avg_flight_hours' => 'decimal:1',
        ];
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function countryTreatments(): HasMany
    {
        return $this->hasMany(CountryTreatment::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }
}
