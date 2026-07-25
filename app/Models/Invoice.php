<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * See docs/05-database-schema-erd.md. Bills a subscription or a commission
 * (polymorphic `billable`), or stands alone. `public_id` (UUID) is the
 * external handle; `number` is the human sequential reference generated in
 * App\Actions\Billing\GenerateInvoice.
 */
class Invoice extends Model
{
    protected $fillable = [
        'public_id', 'number', 'billable_type', 'billable_id', 'clinic_id',
        'amount', 'tax', 'total', 'currency', 'status',
        'issued_at', 'due_at', 'paid_at', 'pdf_path', 'provider_ref',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            $invoice->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'due_at' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
