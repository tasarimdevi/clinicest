<?php

declare(strict_types=1);

namespace App\Ai;

use App\Ai\Contracts\AiProvider;
use InvalidArgumentException;

/**
 * Central entry point for all AI features. Resolves the configured provider,
 * merges guardrails, and is the single place feature classes call through.
 * See docs/07-ai-architecture.md §1 for the full platform design (RAG,
 * tool-calling, PII redaction, cost controls — to be layered on as features
 * are built in Phase 2+, per docs/10-roadmap.md).
 */
class AiService
{
    public function __construct(
        protected readonly array $config,
    ) {}

    public function provider(?string $name = null): AiProvider
    {
        $name ??= $this->config['default'];

        $definition = $this->config['providers'][$name]
            ?? throw new InvalidArgumentException("Unknown AI provider [{$name}].");

        $driverClass = $definition['driver'];

        return app($driverClass, ['config' => $definition]);
    }

    public function featureConfig(string $feature): array
    {
        return $this->config['features'][$feature]
            ?? throw new InvalidArgumentException("Unknown AI feature [{$feature}].");
    }
}
