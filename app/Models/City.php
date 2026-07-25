<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

/**
 * One of the five Meilisearch indexes in docs/05-database-schema-erd.md
 * §4, kept correct even though no dedicated city-search UI exists yet —
 * city is only a dropdown filter on the clinic directory today.
 */
class City extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['country_id', 'name', 'slug', 'lat', 'lng', 'airport_code'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    public function searchableAs(): string
    {
        return 'cities';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country?->name,
            'country_id' => $this->country_id,
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('country');
    }
}
