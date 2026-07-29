<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use App\Ai\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Groq provider adapter (OpenAI-compatible chat completions API). Requested
 * explicitly via AiService::provider('groq') by the chat assistant — the
 * platform default stays 'claude' (config/ai.php), so adding this never
 * silently repoints any other AI feature.
 */
class GroqProvider implements AiProvider
{
    private const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        protected readonly array $config,
    ) {}

    public function complete(array $messages, array $options = []): string
    {
        return $this->chat($messages, $options)['choices'][0]['message']['content'] ?? '';
    }

    public function stream(array $messages, array $options = []): iterable
    {
        // The chat assistant's guardrail requires a fully-generated response
        // before anything reaches the user (see ChatResponseVerifier) — there
        // is no real token-by-token streaming, so this yields the completed
        // text as a single chunk purely to satisfy the interface contract.
        yield $this->complete($messages, $options);
    }

    /**
     * Full chat-completions call, returning the raw decoded response (message
     * content, tool_calls, usage). Called directly by chat assistant code
     * that needs tool-calling — App\Ai\Contracts\AiProvider's plain
     * string-returning complete() can't express tool_calls, so this is a
     * Groq-specific method beyond the shared interface.
     *
     * @param  array<int, array{role: string, content: ?string}>  $messages
     * @return array{choices: array<int, array>, usage?: array{total_tokens: int}}
     */
    public function chat(array $messages, array $options = []): array
    {
        $apiKey = $this->config['api_key']
            ?? throw new RuntimeException('GROQ_API_KEY is not configured.');

        $payload = array_filter([
            'model' => $options['model'] ?? $this->config['model'],
            'messages' => $messages,
            'tools' => $options['tools'] ?? null,
            'tool_choice' => $options['tool_choice'] ?? null,
            'max_tokens' => $options['max_tokens'] ?? 1024,
            'temperature' => $options['temperature'] ?? 0.4,
        ], static fn ($value) => $value !== null);

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            $body = substr($response->body(), 0, 300);

            throw new RuntimeException("Groq API request failed: {$response->status()} {$body}");
        }

        return $response->json();
    }
}
