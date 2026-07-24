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
}
