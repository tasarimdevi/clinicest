<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'name', 'slug', 'lat', 'lng', 'airport_code'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }
}
