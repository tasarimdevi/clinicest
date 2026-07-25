<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Leads\HandleSlaBreach;
use App\Models\LeadAssignment;
use Illuminate\Console\Command;

/**
 * docs/09-crm-admin-architecture.md §2's SLA rule: a clinic has a fixed
 * window (config('clinicest.lead_sla_hours')) to accept/decline an offered
 * lead before it's auto-reassigned. This is the scheduled enforcer —
 * every still-'offered' assignment past its sla_due_at is a breach.
 *
 * Idempotent: it only touches offered+overdue rows, and HandleSlaBreach
 * flips each to 'expired', so a second run in the same window is a no-op.
 * A reassignment gets a fresh future sla_due_at, so it can't be re-breached
 * within the same run.
 */
class EnforceLeadSla extends Command
{
    protected $signature = 'leads:enforce-sla';

    protected $description = 'Expire and auto-reassign lead offers whose response SLA has lapsed';

    public function handle(HandleSlaBreach $handleSlaBreach): int
    {
        $breached = LeadAssignment::query()
            ->where('status', 'offered')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->with(['lead', 'clinic'])
            ->get();

        foreach ($breached as $assignment) {
            $handleSlaBreach->handle($assignment);
        }

        $this->info("Processed {$breached->count()} breached assignment(s).");

        return self::SUCCESS;
    }
}
