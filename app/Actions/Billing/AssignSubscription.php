<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Clinic;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

/**
 * Puts a clinic on a plan. Any existing live (trialing/active)
 * subscription is canceled first, so a clinic has at most one live row —
 * the switch is recorded as a new row rather than mutating the old one,
 * preserving history (docs/05 keeps the subscription list).
 *
 * status is set to 'active' directly (no trial period modeled) and
 * renews_at to one month out purely as a record — nothing auto-charges or
 * auto-renews, since no payment provider is wired (deterministic pass).
 */
class AssignSubscription
{
    public function handle(Clinic $clinic, SubscriptionPlan $plan): Subscription
    {
        return DB::transaction(function () use ($clinic, $plan) {
            $clinic->subscriptions()
                ->whereIn('status', ['trialing', 'active'])
                ->update(['status' => 'canceled', 'canceled_at' => now()]);

            return $clinic->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'started_at' => now(),
                'renews_at' => now()->addMonth(),
            ]);
        });
    }
}
