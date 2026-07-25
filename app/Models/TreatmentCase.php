<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TreatmentCaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class TreatmentCase extends Model
{
    protected $fillable = [
        'public_id', 'lead_id', 'clinic_id', 'doctor_id', 'treatment_ids_json',
        'agreed_price', 'currency', 'status', 'arrival_date', 'completion_date', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $treatmentCase) {
            $treatmentCase->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'treatment_ids_json' => 'array',
            'arrival_date' => 'date',
            'completion_date' => 'date',
            'status' => TreatmentCaseStatus::class,
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

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }
}
