<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use App\Ai\Contracts\AiProvider;
use RuntimeException;

/**
 * Claude provider adapter. Wire this to the Anthropic SDK when the AI
 * features are implemented (docs/10-roadmap.md Phase 2+). Left as a
 * scaffold so app/Ai/AiService.php has a concrete, resolvable driver.
 */
class ClaudeProvider implements AiProvider
{
    public function __construct(
        protected readonly array $config,
    ) {}

    public function complete(array $messages, array $options = []): string
    {
        throw new RuntimeException('ClaudeProvider::complete() is not yet implemented. See docs/07-ai-architecture.md.');
    }

    public function stream(array $messages, array $options = []): iterable
    {
        throw new RuntimeException('ClaudeProvider::stream() is not yet implemented. See docs/07-ai-architecture.md.');
    }
}
