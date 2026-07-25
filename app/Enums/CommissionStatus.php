<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending = 'pending';
    case Invoiced = 'invoiced';
    case Paid = 'paid';
    case Waived = 'waived';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Invoiced => 'Invoiced',
            self::Paid => 'Paid',
            self::Waived => 'Waived',
            self::Disputed => 'Disputed',
        };
    }
}
