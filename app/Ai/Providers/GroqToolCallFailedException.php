<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use RuntimeException;

/**
 * llama-3.3-70b-versatile (via Groq) frequently reverts to its own native
 * <function=name>{args}</function> tag syntax instead of emitting a
 * structured tool_calls entry, and Groq's API rejects the whole turn with a
 * 400 "tool_use_failed" when that happens — empirically, on close to every
 * turn that involves a tool at all. $failedGeneration carries the raw
 * malformed text so ChatConversationService can parse the intended call out
 * of it and recover, rather than treating this as a hard failure.
 */
class GroqToolCallFailedException extends RuntimeException
{
    public function __construct(public readonly string $failedGeneration)
    {
        parent::__construct('Groq rejected a native-format tool call: '.$failedGeneration);
    }
}
