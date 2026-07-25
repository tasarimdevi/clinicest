<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $fillable = [
        'public_id', 'lead_id', 'clinic_id', 'doctor_id', 'type',
        'scheduled_at', 'timezone', 'status', 'meeting_url', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $appointment) {
            $appointment->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => AppointmentType::class,
            'scheduled_at' => 'datetime',
            'status' => AppointmentStatus::class,
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
