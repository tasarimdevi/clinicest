<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors app/Actions/Offers/CreateOffer.php's shape. Does not advance
 * Lead status — the LeadStatus enum (New..Won/Lost/Invalid) has no
 * "appointment scheduled" stage, and adding one is a bigger change than
 * this pass's scope; the appointment's own status plus the logged
 * LeadActivity are the record of it happening.
 */
class RequestAppointment
{
    /**
     * @param  array{
     *     doctor_id?: int|null,
     *     type: string,
     *     scheduled_at: string,
     *     timezone: string,
     *     meeting_url?: string|null,
     *     notes?: string|null,
     * } $data
     */
    public function handle(Lead $lead, Clinic $clinic, array $data, User $requestedBy): Appointment
    {
        return DB::transaction(function () use ($lead, $clinic, $data, $requestedBy) {
            $appointment = Appointment::create([
                'lead_id' => $lead->id,
                'clinic_id' => $clinic->id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'type' => $data['type'],
                'scheduled_at' => $data['scheduled_at'],
                'timezone' => $data['timezone'],
                'meeting_url' => $data['meeting_url'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => AppointmentStatus::Requested,
            ]);

            $lead->activities()->create([
                'actor_type' => $requestedBy::class,
                'actor_id' => $requestedBy->id,
                'type' => 'system',
                'payload_json' => ['event' => 'appointment_requested', 'appointment_id' => $appointment->id, 'clinic_id' => $clinic->id],
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }
}
