<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The paid subscription tiers from docs/01-product-strategy.md §3
 * (Verified / Growth / Elite). Deliberately separate from
 * App\Enums\VerificationTier — that's the trust badge earned through the
 * verification standard, this is a plan a clinic pays for. The name
 * overlap ("Elite") is coincidental and intentional in the docs.
 */
enum SubscriptionTier: string
{
    case Verified = 'verified';
    case Growth = 'growth';
    case Elite = 'elite';

    public function label(): string
    {
        return match ($this) {
            self::Verified => 'Verified',
            self::Growth => 'Growth',
            self::Elite => 'Elite',
        };
    }
}
