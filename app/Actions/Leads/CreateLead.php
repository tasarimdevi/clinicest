<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

/**
 * Entry point for the primary conversion funnel: a visitor's free-consultation
 * request becomes a Lead. See docs/02-information-architecture-ux.md §4.
 */
class CreateLead
{
    /**
     * @param  array{
     *     full_name: string,
     *     email: string,
     *     phone?: string|null,
     *     whatsapp?: string|null,
     *     country_id?: int|null,
     *     primary_treatment_id?: int|null,
     *     treatments_json?: array|null,
     *     budget_min?: int|null,
     *     budget_max?: int|null,
     *     currency?: string|null,
     *     timeline?: string|null,
     *     message?: string|null,
     *     source?: array|null,
     *     channel?: string|null,
     *     locale?: string|null,
     *     user_id?: int|null,
     *     consent: array{granted: bool, text_version: string, ip: ?string, user_agent: ?string},
     * } $data
     */
    public function handle(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::create([
                'user_id' => $data['user_id'] ?? null,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'country_id' => $data['country_id'] ?? null,
                'primary_treatment_id' => $data['primary_treatment_id'] ?? null,
                'treatments_json' => $data['treatments_json'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'currency' => $data['currency'] ?? 'EUR',
                'timeline' => $data['timeline'] ?? null,
                'message' => $data['message'] ?? null,
                'source' => $data['source'] ?? null,
                'channel' => $data['channel'] ?? 'web',
                'status' => LeadStatus::New,
                'locale' => $data['locale'] ?? app()->getLocale(),
            ]);

            $lead->consents()->create([
                'type' => 'data_processing',
                'granted' => $data['consent']['granted'],
                'text_version' => $data['consent']['text_version'],
                'ip' => $data['consent']['ip'] ?? null,
                'user_agent' => $data['consent']['user_agent'] ?? null,
                'granted_at' => $data['consent']['granted'] ? now() : null,
            ]);

            $lead->activities()->create([
                'type' => 'system',
                'payload_json' => ['event' => 'lead_created', 'channel' => $lead->channel],
                'created_at' => now(),
            ]);

            return $lead;
        });
    }
}
