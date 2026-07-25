<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Models\Clinic;
use App\Models\LeadAssignment;
use App\Models\User;
use App\Notifications\LeadAssignedToClinic;
use App\Notifications\LeadSlaBreached;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Handles one breached assignment (status=offered, past sla_due_at). See
 * docs/09-crm-admin-architecture.md §2: a clinic that doesn't respond in
 * time has the lead auto-reassigned.
 *
 * Deterministic reassignment (no ML matching): the next active clinic that
 * offers the lead's primary treatment and hasn't already been assigned
 * this lead, best-rated first. The lead_assignments unique(lead_id,
 * clinic_id) constraint plus the "not already assigned" filter bound the
 * bounce — a lead can be reassigned at most once per remaining eligible
 * clinic, then the pool is exhausted and it falls to manual attention.
 */
class HandleSlaBreach
{
    public function handle(LeadAssignment $assignment): void
    {
        $lead = $assignment->lead;
        $lapsedClinic = $assignment->clinic;
        $slaHours = (int) config('clinicest.lead_sla_hours', 24);

        $reassignedTo = DB::transaction(function () use ($assignment, $lead, $slaHours) {
            $assignment->update(['status' => 'expired']);

            $lead->activities()->create([
                'actor_type' => null,
                'actor_id' => null,
                'type' => 'assignment',
                'payload_json' => ['event' => 'sla_breach', 'clinic_id' => $assignment->clinic_id],
                'created_at' => now(),
            ]);

            // Only re-offer an open lead; a won/lost/invalid lead's stale
            // assignment is just expired for housekeeping, not re-pushed.
            if (! $lead->status->isOpen()) {
                return null;
            }

            $candidate = $this->findCandidate($lead);

            if ($candidate === null) {
                // Nothing to hand it to — surface it back to the agents by
                // returning it to the assignable pool if nothing else is live.
                if (! $this->hasLiveAssignment($lead)) {
                    $lead->update(['status' => LeadStatus::Qualified]);
                }

                return null;
            }

            $lead->assignments()->create([
                'clinic_id' => $candidate->id,
                'assigned_by' => null, // system reassignment, no acting user
                'status' => 'offered',
                'assigned_at' => now(),
                'sla_due_at' => now()->addHours($slaHours),
            ]);

            $lead->activities()->create([
                'actor_type' => null,
                'actor_id' => null,
                'type' => 'assignment',
                'payload_json' => ['event' => 'sla_reassign', 'from_clinic_id' => $assignment->clinic_id, 'to_clinic_id' => $candidate->id],
                'created_at' => now(),
            ]);

            if ($lead->status === LeadStatus::Qualified) {
                $lead->update(['status' => LeadStatus::Assigned]);
            }

            return $candidate;
        });

        if ($reassignedTo) {
            Notification::send(
                $reassignedTo->usersWithPermission('leads.view'),
                new LeadAssignedToClinic($lead, $reassignedTo)
            );
        }

        Notification::send(
            User::permission('leads.assign')->get(),
            new LeadSlaBreached($lead, $lapsedClinic, $reassignedTo)
        );
    }

    protected function findCandidate($lead): ?Clinic
    {
        $alreadyAssigned = $lead->assignments()->pluck('clinic_id');

        return Clinic::query()
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyAssigned)
            ->when($lead->primary_treatment_id, fn ($q) => $q->whereHas(
                'treatments',
                fn ($t) => $t->where('treatments.id', $lead->primary_treatment_id)->where('clinic_treatment.is_available', true)
            ))
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->first();
    }

    protected function hasLiveAssignment($lead): bool
    {
        return $lead->assignments()->whereIn('status', ['offered', 'accepted'])->exists();
    }
}
