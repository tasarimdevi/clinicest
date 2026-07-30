<?php

declare(strict_types=1);

namespace App\Ai;

use App\Actions\Leads\CreateLead;
use App\Models\ChatSession;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Treatment;
use App\Services\CostEstimatorService;

/**
 * Function-calling tools the chat assistant can invoke. Every tool is a thin
 * wrapper around existing services — the chatbot never reimplements pricing
 * or lead-creation logic (docs/07-ai-architecture.md §2.4: "tool-calls for
 * clinic/price lookups; hand-off to CRM"). list_clinics deliberately returns
 * only aggregate/directory info, never a single named clinic, so the
 * no-recommendation rule is enforced structurally, not just by prompt.
 */
class ChatTools
{
    public function __construct(
        protected readonly CostEstimatorService $estimator,
        protected readonly CreateLead $createLeadAction,
    ) {}

    /** @return array<int, array<string, mixed>> Groq/OpenAI tool schema */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_treatments',
                    'description' => "Search Clinicest's published treatments by keyword to find matching options and their slugs.",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Keyword, e.g. "implant", "veneers", "all-on-4"'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_cost_estimate',
                    'description' => "Get Clinicest's own price-range estimate (Turkey vs. home country) for a treatment, by treatment slug and optional 2-letter country code. Always call search_treatments first to get a real slug — never guess one.",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'treatment_slug' => ['type' => 'string'],
                            'country_code' => ['type' => 'string', 'description' => 'ISO 3166-1 alpha-2, e.g. "GB", "DE"'],
                        ],
                        'required' => ['treatment_slug'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_clinics',
                    'description' => 'Get a general, neutral overview of verified clinics available (counts + directory link only) — never a single named recommendation.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'treatment_slug' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_lead',
                    'description' => 'Submit the visitor as a free-quote lead. Only call this once you have their name and email AND they have explicitly agreed to be contacted.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'full_name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'whatsapp' => ['type' => 'string'],
                            'treatment_slug' => ['type' => 'string'],
                            'message' => ['type' => 'string'],
                        ],
                        'required' => ['full_name', 'email'],
                    ],
                ],
            ],
        ];
    }

    /** @return array{result: array<string, mixed>, amounts: array<int, float>} */
    public function call(string $name, array $arguments, ChatSession $session): array
    {
        return match ($name) {
            'search_treatments' => $this->searchTreatments($arguments),
            'get_cost_estimate' => $this->getCostEstimate($arguments),
            'list_clinics' => $this->listClinics($arguments),
            'create_lead' => $this->submitLead($arguments, $session),
            default => ['result' => ['error' => "unknown tool: {$name}"], 'amounts' => []],
        };
    }

    private function searchTreatments(array $args): array
    {
        $query = (string) ($args['query'] ?? '');

        // The model doesn't reliably pass a single keyword ("implant") over
        // a short phrase ("implant prices") — matching on the whole phrase
        // as one substring would miss "Dental Implants" entirely, so match
        // on any individual word instead.
        $words = array_filter(preg_split('/\s+/', trim($query)) ?: []);

        $treatments = Treatment::query()
            ->where('status', 'published')
            ->where(function ($q) use ($words, $query) {
                $q->where('name', 'like', "%{$query}%")->orWhere('summary', 'like', "%{$query}%");

                foreach ($words as $word) {
                    $q->orWhere('name', 'like', "%{$word}%")->orWhere('summary', 'like', "%{$word}%");
                }
            })
            ->limit(5)
            ->get();

        return [
            'result' => [
                'treatments' => $treatments->map(fn (Treatment $t) => [
                    'slug' => $t->slug,
                    'name' => $t->getTranslation('name', app()->getLocale()),
                    'summary' => $t->getTranslation('summary', app()->getLocale()),
                    'url' => route('treatments.show', $t),
                ])->all(),
            ],
            'amounts' => [],
        ];
    }

    private function getCostEstimate(array $args): array
    {
        $treatment = Treatment::query()
            ->where('slug', $args['treatment_slug'] ?? null)
            ->where('status', 'published')
            ->first();

        if (! $treatment) {
            return ['result' => ['error' => 'treatment not found'], 'amounts' => []];
        }

        $country = isset($args['country_code'])
            ? Country::query()->where('iso2', strtoupper((string) $args['country_code']))->where('is_target', true)->first()
            : null;

        $estimate = $this->estimator->estimate($treatment, $country);

        $amounts = array_values(array_filter([
            $estimate['turkey_min'] ?? null,
            $estimate['turkey_max'] ?? null,
            $estimate['local_min'] ?? null,
            $estimate['local_max'] ?? null,
        ], static fn ($v) => $v !== null));

        return ['result' => $estimate, 'amounts' => array_map(static fn ($v) => (float) $v, $amounts)];
    }

    private function listClinics(array $args): array
    {
        $query = Clinic::query()->where('is_active', true);

        if (! empty($args['treatment_slug'])) {
            $treatment = Treatment::query()->where('slug', $args['treatment_slug'])->first();

            if ($treatment) {
                $query->whereHas('treatments', fn ($q) => $q->where('treatments.id', $treatment->id));
            }
        }

        return [
            'result' => [
                'verified_clinic_count' => $query->count(),
                'directory_url' => url('/clinics'),
            ],
            'amounts' => [],
        ];
    }

    private function submitLead(array $args, ChatSession $session): array
    {
        if (empty($args['full_name']) || empty($args['email'])) {
            return ['result' => ['error' => 'full_name and email are required'], 'amounts' => []];
        }

        $treatment = isset($args['treatment_slug'])
            ? Treatment::query()->where('slug', $args['treatment_slug'])->first()
            : null;

        $lead = $this->createLeadAction->handle([
            'full_name' => $args['full_name'],
            'email' => $args['email'],
            'whatsapp' => $args['whatsapp'] ?? null,
            'primary_treatment_id' => $treatment?->id,
            'message' => $args['message'] ?? null,
            'channel' => 'ai_advisor',
            'locale' => $session->locale,
            'consent' => [
                'granted' => true,
                'text_version' => 'v1',
                'ip' => null,
                'user_agent' => null,
            ],
        ]);

        $session->update(['lead_id' => $lead->id, 'status' => 'converted']);

        return ['result' => ['lead_created' => true], 'amounts' => []];
    }
}
