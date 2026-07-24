<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reduced-scope pSEO comparison row — see the migration docblock and
 * docs/06-seo-architecture.md §2 for what's deliberately not built yet.
 */
class CountryTreatment extends Model
{
    protected $table = 'country_treatment';

    protected $fillable = [
        'country_id', 'treatment_id', 'currency',
        'local_price_min', 'local_price_max', 'turkey_price_min', 'turkey_price_max',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function savingsPct(): int
    {
        if ($this->local_price_min <= 0) {
            return 0;
        }

        return (int) round((1 - ($this->turkey_price_min / $this->local_price_min)) * 100);
    }
}
