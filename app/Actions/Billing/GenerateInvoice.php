<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Creates an Invoice for a billable (a Commission we charge the clinic, or
 * a Subscription the clinic owes for). The amount is derived from the
 * billable so callers can't drift it. `number` is a human sequential
 * reference (INV-{year}-{nnnn}); `public_id` (UUID) is the external
 * handle. Issued as 'sent' — there's no draft-review step and no PDF
 * generation in this deterministic pass (pdf_path stays null).
 */
class GenerateInvoice
{
    public function handle(Clinic $clinic, Model $billable, int $tax = 0): Invoice
    {
        [$amount, $currency] = match (true) {
            $billable instanceof Commission => [$billable->amount, $billable->currency],
            $billable instanceof Subscription => [$billable->plan->price_month, $billable->plan->currency],
            default => throw new InvalidArgumentException('Unsupported billable: '.$billable::class),
        };

        return DB::transaction(function () use ($clinic, $billable, $amount, $currency, $tax) {
            $sequence = Invoice::whereYear('created_at', now()->year)->count() + 1;

            return Invoice::create([
                'number' => sprintf('INV-%d-%04d', now()->year, $sequence),
                'billable_type' => $billable::class,
                'billable_id' => $billable->id,
                'clinic_id' => $clinic->id,
                'amount' => $amount,
                'tax' => $tax,
                'total' => $amount + $tax,
                'currency' => $currency,
                'status' => 'sent',
                'issued_at' => now(),
                'due_at' => now()->addDays(14),
            ]);
        });
    }
}
