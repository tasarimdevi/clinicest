<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = [
        'treatment_case_id', 'clinic_id', 'base_amount', 'rate_pct', 'amount',
        'currency', 'status', 'due_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate_pct' => 'decimal:2',
            'due_at' => 'date',
            'paid_at' => 'datetime',
            'status' => CommissionStatus::class,
        ];
    }

    public function treatmentCase(): BelongsTo
    {
        return $this->belongsTo(TreatmentCase::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
