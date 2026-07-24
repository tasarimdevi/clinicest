<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'iso2', 'iso3', 'name', 'slug', 'currency', 'dial_code',
        'flag_path', 'is_target', 'tier',
    ];

    protected function casts(): array
    {
        return [
            'is_target' => 'boolean',
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
}
