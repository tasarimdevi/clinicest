<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * The three plan tiers named in docs/01-product-strategy.md §3
 * (Verified / Growth / Elite). docs states the tiers but not concrete
 * prices, so the amounts below are PLACEHOLDERS (integer minor units,
 * EUR) — sensible defaults for local dev, not figures the business has
 * committed to. Idempotent (updateOrCreate by slug) so it can seed a
 * fresh env or top up an existing one.
 */
class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Verified', 'slug' => 'verified', 'tier' => 'verified',
                'price_month' => 0, 'price_year' => 0, 'lead_quota' => 5, 'sort' => 1,
                'features_json' => ['Listed in the directory', 'Verification badge', 'Up to 5 leads / month'],
            ],
            [
                'name' => 'Growth', 'slug' => 'growth', 'tier' => 'growth',
                'price_month' => 19900, 'price_year' => 199000, 'lead_quota' => 30, 'sort' => 2,
                'features_json' => ['Everything in Verified', 'Priority placement eligibility', 'Up to 30 leads / month', 'Basic analytics'],
            ],
            [
                'name' => 'Elite', 'slug' => 'elite', 'tier' => 'elite',
                'price_month' => 49900, 'price_year' => 499000, 'lead_quota' => null, 'sort' => 3,
                'features_json' => ['Everything in Growth', 'Top placement eligibility', 'Unlimited leads', 'Full analytics', 'Priority support'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                [...$plan, 'currency' => 'EUR', 'is_active' => true],
            );
        }
    }
}
