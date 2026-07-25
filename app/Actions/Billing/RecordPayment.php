<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Records that an invoice was paid. In this deterministic pass this is a
 * manual staff action (a bank transfer cleared), not a gateway callback —
 * provider defaults to 'manual', status 'succeeded'. Marks the invoice
 * paid and, when the invoice bills a Commission, advances that commission
 * to 'paid' too so the two stay in lockstep (a commission is only truly
 * settled once its invoice is).
 */
class RecordPayment
{
    public function handle(Invoice $invoice, string $method = 'bank_transfer', string $provider = 'manual'): Payment
    {
        return DB::transaction(function () use ($invoice, $method, $provider) {
            $payment = $invoice->payments()->create([
                'provider' => $provider,
                'amount' => $invoice->total,
                'currency' => $invoice->currency,
                'status' => 'succeeded',
                'method' => $method,
                'paid_at' => now(),
            ]);

            $invoice->update(['status' => 'paid', 'paid_at' => now()]);

            if ($invoice->billable_type === Commission::class && $invoice->billable) {
                $invoice->billable->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return $payment;
        });
    }
}
