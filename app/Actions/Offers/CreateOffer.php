<?php

declare(strict_types=1);

namespace App\Actions\Offers;

use App\Enums\LeadStatus;
use App\Enums\OfferStatus;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors app/Actions/Leads/AssignLeadToClinics.php's shape: wraps the
 * mutation in a transaction, advances the lead status, and logs a matching
 * LeadActivity row. See docs/09-crm-admin-architecture.md §3.
 */
class CreateOffer
{
    /**
     * @param  array{
     *     doctor_id?: int|null,
     *     title: string,
     *     treatment_plan?: string|null,
     *     price_total: int,
     *     currency: string,
     *     breakdown_json?: array|null,
     *     includes_json?: array|null,
     *     valid_until?: string|null,
     * } $data
     */
    public function handle(Lead $lead, Clinic $clinic, array $data, User $createdBy): Offer
    {
        return DB::transaction(function () use ($lead, $clinic, $data, $createdBy) {
            $offer = Offer::create([
                'lead_id' => $lead->id,
                'clinic_id' => $clinic->id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'title' => $data['title'],
                'treatment_plan' => $data['treatment_plan'] ?? null,
                'price_total' => $data['price_total'],
                'currency' => $data['currency'],
                'breakdown_json' => $data['breakdown_json'] ?? null,
                'includes_json' => $data['includes_json'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => OfferStatus::Sent,
            ]);

            if ($lead->status === LeadStatus::Assigned) {
                $lead->update(['status' => LeadStatus::OfferSent]);
            }

            $lead->activities()->create([
                'actor_type' => $createdBy::class,
                'actor_id' => $createdBy->id,
                'type' => 'system',
                'payload_json' => ['event' => 'offer_sent', 'offer_id' => $offer->id, 'clinic_id' => $clinic->id],
                'created_at' => now(),
            ]);

            return $offer;
        });
    }
}
