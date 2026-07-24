<?php

declare(strict_types=1);
use App\Ai\Providers\ClaudeProvider;

/*
|--------------------------------------------------------------------------
| AI configuration
|--------------------------------------------------------------------------
| See docs/07-ai-architecture.md. Provider-agnostic: features reference a
| named provider + model here, never a hardcoded SDK call, so the backend
| can be swapped without touching feature code.
*/

return [

    'default' => env('AI_PROVIDER', 'claude'),

    'providers' => [
        'claude' => [
            'driver' => ClaudeProvider::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('AI_CLAUDE_MODEL', 'claude-opus-4-8'),
            'fast_model' => env('AI_CLAUDE_FAST_MODEL', 'claude-haiku-4-5-20251001'),
        ],
    ],

    'embeddings' => [
        'driver' => env('AI_EMBEDDINGS_DRIVER', 'openai'),
        'dim' => 1536,
    ],

    /*
    | Per-feature routing: which model tier + guardrail + behaviour each
    | AI feature uses. See docs/07-ai-architecture.md §2 for feature specs.
    */
    'features' => [
        'advisor' => [
            'model' => 'reasoning',
            'stream' => true,
            'guardrail' => 'medical',
        ],
        'clinic_matching' => [
            'model' => 'reasoning',
            'guardrail' => 'medical',
        ],
        'cost_estimator' => [
            'model' => 'fast',
            'guardrail' => 'medical',
        ],
        'chat_assistant' => [
            'model' => 'reasoning',
            'stream' => true,
            'guardrail' => 'medical',
        ],
        'review_summary' => [
            'model' => 'fast',
            'cache' => true,
        ],
        'faq_generator' => [
            'model' => 'reasoning',
            'review_required' => true,
        ],
        'seo_assistant' => [
            'model' => 'reasoning',
            'review_required' => true,
        ],
        'translation' => [
            'model' => 'reasoning',
            'review_required' => ['en', 'de', 'tr'],
        ],
    ],

];
