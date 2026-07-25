<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Mirrors OfferPolicy's shape. Unlike an offer's accept/reject (inherently
 * the patient's call), confirming/rescheduling/cancelling an appointment
 * is something the clinic itself does (docs/09-crm-admin-architecture.md
 * §3: "confirm/reschedule remote consults & on-site visits") — so
 * `update` is not a patient-portal stand-in here, it's the clinic's normal
 * workflow. Admin also gets it for CRM oversight, same as offers.
 */
class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('appointments.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->can('appointments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('appointments.manage');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('appointments.manage');
    }
}
