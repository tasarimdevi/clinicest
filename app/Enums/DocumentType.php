<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case TreatmentPlan = 'treatment_plan';
    case Xray = 'xray';
    case Invoice = 'invoice';
    case Certificate = 'certificate';
    case Verification = 'verification';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TreatmentPlan => 'Treatment Plan',
            self::Xray => 'X-Ray / Scan',
            self::Invoice => 'Invoice',
            self::Certificate => 'Certificate',
            self::Verification => 'Verification Document',
            self::Other => 'Other',
        };
    }
}
