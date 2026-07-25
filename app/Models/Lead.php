<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id', 'user_id', 'full_name', 'email', 'phone', 'whatsapp',
        'country_id', 'primary_treatment_id', 'treatments_json',
        'budget_min', 'budget_max', 'currency', 'timeline', 'message',
        'source', 'channel', 'status', 'score', 'assigned_agent_id',
        'quality', 'locale', 'first_response_at', 'closed_at', 'lost_reason',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            $lead->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'treatments_json' => 'array',
            'source' => 'array',
            'status' => LeadStatus::class,
            'first_response_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function primaryTreatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'primary_treatment_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(LeadMedia::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function consents(): MorphMany
    {
        return $this->morphMany(Consent::class, 'consentable');
    }
}
