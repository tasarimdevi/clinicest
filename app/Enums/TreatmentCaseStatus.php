<?php

declare(strict_types=1);

namespace App\Enums;

enum TreatmentCaseStatus: string
{
    case Planned = 'planned';
    case InTreatment = 'in_treatment';
    case Completed = 'completed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::InTreatment => 'In Treatment',
            self::Completed => 'Completed',
            self::Refunded => 'Refunded',
        };
    }
}
