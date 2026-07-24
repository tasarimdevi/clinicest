<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Qualifying = 'qualifying';
    case Qualified = 'qualified';
    case Assigned = 'assigned';
    case OfferSent = 'offer_sent';
    case Negotiating = 'negotiating';
    case Won = 'won';
    case Lost = 'lost';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Qualifying => 'Qualifying',
            self::Qualified => 'Qualified',
            self::Assigned => 'Assigned',
            self::OfferSent => 'Offer Sent',
            self::Negotiating => 'Negotiating',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Invalid => 'Invalid',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Won, self::Lost, self::Invalid], true);
    }
}
