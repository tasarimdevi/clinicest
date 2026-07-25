<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Country;
use App\Models\CountryTreatment;
use App\Models\Treatment;

/**
 * Backs the "AI Cost Estimator" (docs/07-ai-architecture.md §2.3). Despite
 * the feature name, the estimate itself is deterministic — built from our
 * own clinic_treatment/country_treatment price data, exactly as the docs
 * specify ("rules/data-driven ... always a range + final price after
 * clinic review"). The AI part of that spec (parsing free-text/photos into
 * a treatment guess) needs a live LLM call and is not built yet — there is
 * no ANTHROPIC_API_KEY configured in this environment (see config/ai.php,
 * app/Ai/Providers/ClaudeProvider.php). This service is the honest,
 * fully-working slice; free-text intake can be added later as an input
 * path into the same estimate() call.
 */
class CostEstimatorService
{
    /**
     * @return array{
     *     currency: string,
     *     turkey_min: int, turkey_max: int,
     *     local_min: ?int, local_max: ?int,
     *     savings_pct: ?int,
     *     source: 'country_treatment'|'treatment_base'|null,
     * }
     */
    public function estimate(Treatment $treatment, ?Country $country): array
    {
        if ($country) {
            /** @var CountryTreatment|null $row */
            $row = CountryTreatment::query()
                ->where('treatment_id', $treatment->id)
                ->where('country_id', $country->id)
                ->first();

            if ($row) {
                return [
                    'currency' => $row->currency,
                    'turkey_min' => $row->turkey_price_min,
                    'turkey_max' => $row->turkey_price_max,
                    'local_min' => $row->local_price_min,
                    'local_max' => $row->local_price_max,
                    'savings_pct' => $row->savingsPct(),
                    'source' => 'country_treatment',
                ];
            }
        }

        if ($treatment->base_price_min === null || $treatment->base_price_max === null) {
            return [
                'currency' => $treatment->currency ?? 'EUR',
                'turkey_min' => 0, 'turkey_max' => 0,
                'local_min' => null, 'local_max' => null,
                'savings_pct' => null,
                'source' => null,
            ];
        }

        return [
            'currency' => $treatment->currency ?? 'EUR',
            'turkey_min' => $treatment->base_price_min,
            'turkey_max' => $treatment->base_price_max,
            'local_min' => null,
            'local_max' => null,
            'savings_pct' => null,
            'source' => 'treatment_base',
        ];
    }
}
