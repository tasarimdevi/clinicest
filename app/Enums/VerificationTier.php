<?php

declare(strict_types=1);

namespace App\Enums;

enum VerificationTier: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case VerifiedPlus = 'verified_plus';
    case Elite = 'elite';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Verified => 'Verified',
            self::VerifiedPlus => 'Verified+',
            self::Elite => 'Elite Partner',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this !== self::Pending;
    }
}
