<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Offer extends Model
{
    protected $fillable = [
        'public_id', 'lead_id', 'clinic_id', 'doctor_id', 'title', 'treatment_plan',
        'price_total', 'currency', 'breakdown_json', 'includes_json', 'valid_until', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $offer) {
            $offer->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'breakdown_json' => 'array',
            'includes_json' => 'array',
            'valid_until' => 'date',
            'status' => OfferStatus::class,
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
