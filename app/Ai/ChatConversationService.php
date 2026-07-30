<?php

declare(strict_types=1);

namespace App\Ai;

use App\Ai\Guardrails\ChatGuardrail;
use App\Ai\Guardrails\ChatResponseVerifier;
use App\Ai\Providers\GroqProvider;
use App\Ai\Support\PiiRedactor;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs the Groq round-trip(s) for one chat turn — looping through up to
 * MAX_TOOL_ROUNDS of tool calls, since models routinely chain more than one
 * — and the numeric-claim guardrail. Extracted out of ChatWidget so
 * ProcessChatReply (queued) and the Livewire component don't duplicate this
 * logic; runs on the queue worker, not the web request, so a slow Groq call
 * never ties up an Apache prefork worker for its whole duration (see
 * app/Jobs/ProcessChatReply.php docblock).
 *
 * Never lets a Groq/tool failure crash the caller: a malformed tool call, a
 * transient API error, or the model exhausting its tool-call rounds without
 * ever producing text all degrade to a safe fallback message.
 */
class ChatConversationService
{
    // Kept low (3 rounds x GroqProvider's per-call timeout) so worst-case
    // latency stays well under the proxy/client's patience on this
    // memory-constrained host.
    private const MAX_TOOL_ROUNDS = 3;

    /**
     * @return array{content: string, draft: string, flagged: bool, flag_reason: ?string,
     *     tool_name: ?string, tool_input: ?array, tool_output: ?array, model: ?string, tokens_used: int}
     */
    public function converse(ChatTools $tools, ChatSession $session): array
    {
        $fallback = fn (string $reason) => [
            'content' => ChatResponseVerifier::fallback(),
            'draft' => '',
            'flagged' => true,
            'flag_reason' => $reason,
            'tool_name' => null,
            'tool_input' => null,
            'tool_output' => null,
            'model' => null,
            'tokens_used' => 0,
        ];

        try {
            $groqMessages = PiiRedactor::redactMessages($this->buildHistory($session));

            /** @var GroqProvider $groq */
            $groq = app(AiService::class)->provider('groq');

            $amounts = [];
            $toolName = null;
            $toolInput = null;
            $toolOutput = null;
            $response = [];
            $choice = [];

            for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
                $response = $groq->chat($groqMessages, ['tools' => $tools->definitions(), 'tool_choice' => 'auto']);
                $choice = $response['choices'][0]['message'] ?? [];

                if (empty($choice['tool_calls'])) {
                    break;
                }

                $groqMessages[] = $choice;

                foreach ($choice['tool_calls'] as $call) {
                    $name = $call['function']['name'] ?? '';
                    $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];

                    $result = $tools->call($name, $arguments, $session);
                    $amounts = array_merge($amounts, $result['amounts']);
                    $toolName = $name;
                    $toolInput = $arguments;
                    $toolOutput = $result['result'];

                    $groqMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'] ?? '',
                        'content' => json_encode($result['result']),
                    ];
                }
            }

            $draft = (string) ($choice['content'] ?? '');

            if ($draft === '') {
                return $fallback('model exhausted tool-call rounds without a text reply');
            }
            $verified = (new ChatResponseVerifier)->verify($draft, $amounts);

            return [
                'content' => $verified['content'],
                'draft' => $draft,
                'flagged' => $verified['flagged'],
                'flag_reason' => $verified['flag_reason'],
                'tool_name' => $toolName,
                'tool_input' => $toolInput,
                'tool_output' => $toolOutput,
                'model' => $response['model'] ?? null,
                'tokens_used' => (int) ($response['usage']['total_tokens'] ?? 0),
            ];
        } catch (Throwable $e) {
            Log::error('Chat assistant Groq call failed: '.$e->getMessage());

            return $fallback('groq call failed: '.$e->getMessage());
        }
    }

    /** @return array<int, array{role: string, content: string}> */
    private function buildHistory(ChatSession $session): array
    {
        $history = [['role' => 'system', 'content' => ChatGuardrail::preamble()]];

        foreach ($session->messages()->whereIn('role', ['user', 'assistant'])->orderBy('id')->get() as $message) {
            $history[] = ['role' => $message->role, 'content' => $message->content];
        }

        return $history;
    }
}
