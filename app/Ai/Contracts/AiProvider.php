<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

/**
 * Provider-agnostic contract so the LLM backend can be swapped without
 * touching feature code. See docs/07-ai-architecture.md §1.
 */
interface AiProvider
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     */
    public function complete(array $messages, array $options = []): string;

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return iterable<string> streamed text chunks
     */
    public function stream(array $messages, array $options = []): iterable;
}
