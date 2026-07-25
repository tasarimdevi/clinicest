<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentType: string
{
    case RemoteConsult = 'remote_consult';
    case Onsite = 'onsite';

    public function label(): string
    {
        return match ($this) {
            self::RemoteConsult => 'Remote consultation',
            self::Onsite => 'On-site visit',
        };
    }
}
