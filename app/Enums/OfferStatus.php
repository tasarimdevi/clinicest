<?php

declare(strict_types=1);

namespace App\Enums;

enum OfferStatus: string
{
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
