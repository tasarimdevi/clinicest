<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Assigns a lead to one or more clinics, opening the SLA clock on each.
 * See docs/09-crm-admin-architecture.md §2 (assignment & matching).
 */
class AssignLeadToClinics
{
    /**
     * @param  array<int, int>  $clinicIds
     */
    public function handle(Lead $lead, array $clinicIds, User $assignedBy): Lead
    {
        return DB::transaction(function () use ($lead, $clinicIds, $assignedBy) {
            $slaHours = config('clinicest.lead_sla_hours', 24);

            foreach (Clinic::query()->whereKey($clinicIds)->get() as $clinic) {
                $lead->assignments()->updateOrCreate(
                    ['clinic_id' => $clinic->id],
                    [
                        'assigned_by' => $assignedBy->id,
                        'status' => 'offered',
                        'assigned_at' => now(),
                        'sla_due_at' => now()->addHours($slaHours),
                    ]
                );
            }

            if ($lead->status === LeadStatus::New || $lead->status === LeadStatus::Qualifying || $lead->status === LeadStatus::Qualified) {
                $lead->update(['status' => LeadStatus::Assigned]);
            }

            $lead->activities()->create([
                'actor_type' => $assignedBy::class,
                'actor_id' => $assignedBy->id,
                'type' => 'assignment',
                'payload_json' => ['clinic_ids' => $clinicIds],
                'created_at' => now(),
            ]);

            return $lead->fresh();
        });
    }
}
