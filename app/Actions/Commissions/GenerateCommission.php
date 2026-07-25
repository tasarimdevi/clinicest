<?php

declare(strict_types=1);

namespace App\Actions\Commissions;

use App\Models\Commission;
use App\Models\TreatmentCase;

/**
 * docs/09-crm-admin-architecture.md §2: "on treatment_case completion ->
 * CommissionService generates commission (rate by tier/treatment) ->
 * status flow pending->invoiced->paid." Rate-by-tier/treatment is cut —
 * this uses the single global rate already seeded for exactly this
 * purpose (config('clinicest.default_commission_rate'), docs/01
 * §3 "Typical 10-20% depending on treatment value and clinic tier").
 * A per-tier/per-treatment override table is a real future refinement,
 * not built here.
 */
class GenerateCommission
{
    public function handle(TreatmentCase $treatmentCase): Commission
    {
        $ratePct = (float) config('clinicest.default_commission_rate');
        $amount = (int) round($treatmentCase->agreed_price * $ratePct / 100);

        return Commission::create([
            'treatment_case_id' => $treatmentCase->id,
            'clinic_id' => $treatmentCase->clinic_id,
            'base_amount' => $treatmentCase->agreed_price,
            'rate_pct' => $ratePct,
            'amount' => $amount,
            'currency' => $treatmentCase->currency,
            'status' => 'pending',
            'due_at' => now()->addDays(30),
        ]);
    }
}
