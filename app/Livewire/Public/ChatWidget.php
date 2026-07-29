<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Ai\AiService;
use App\Ai\ChatTools;
use App\Ai\Guardrails\ChatGuardrail;
use App\Ai\Guardrails\ChatResponseVerifier;
use App\Ai\Providers\GroqProvider;
use App\Ai\Support\PiiRedactor;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Throwable;

/**
 * Lead-conversion chat widget (docs/07-ai-architecture.md §2.4). One
 * ChatSession per page load — kept deliberately simple, no cross-page-load
 * resume via cookie/localStorage, since a fresh session per visit is enough
 * for the "guide toward /get-quote" goal this was built for.
 *
 * Never streams raw model tokens: ChatResponseVerifier needs the complete
 * response to check numeric claims before anything reaches the user, so the
 * "typing" effect (see chat-widget.blade.php) is a client-side reveal of an
 * already-verified string, not real streaming.
 */
class ChatWidget extends Component
{
    public bool $enabled = false;

    public ?int $chatSessionId = null;

    public string $draft = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public bool $limitReached = false;

    public function mount(): void
    {
        $this->enabled = ChatSetting::current()->enabled;
    }

    protected function rules(): array
    {
        return [
            'draft' => ['required', 'string', 'max:1000'],
        ];
    }

    public function send(ChatTools $tools): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->validate();

        $settings = ChatSetting::current();
        $session = $this->currentSession($settings);

        if ($session === null) {
            $this->limitReached = true;

            return;
        }

        if ($session->message_count >= $settings->max_messages_per_session || ! $settings->hasBudgetRemaining()) {
            $this->limitReached = true;

            return;
        }

        $userText = $this->draft;
        $this->reset('draft');

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $userText,
        ]);

        $this->messages[] = ['role' => 'user', 'content' => $userText];

        $start = microtime(true);
        $outcome = $this->converse($tools, $session);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $outcome['content'],
            'original_draft' => $outcome['flagged'] ? $outcome['draft'] : null,
            'tool_name' => $outcome['tool_name'],
            'tool_input' => $outcome['tool_input'],
            'tool_output' => $outcome['tool_output'],
            'model' => $outcome['model'],
            'latency_ms' => (int) ((microtime(true) - $start) * 1000),
            'flagged' => $outcome['flagged'],
            'flag_reason' => $outcome['flag_reason'],
        ]);

        $session->increment('message_count');
        $session->increment('token_count', $outcome['tokens_used']);
        $settings->recordTokensUsed($outcome['tokens_used']);

        $this->messages[] = ['role' => 'assistant', 'content' => $outcome['content']];
    }

    /**
     * Runs the Groq round-trip(s) (with one tool-call round if the model asks
     * for one) and the numeric-claim guardrail. Never lets a Groq/tool
     * failure crash the request — a malformed tool call or a transient API
     * error degrades to a safe fallback message instead of a 500, since an
     * external LLM call is exactly the kind of thing that can fail in ways
     * this feature must not let take the whole page down with it.
     *
     * @return array{content: string, draft: string, flagged: bool, flag_reason: ?string,
     *     tool_name: ?string, tool_input: ?array, tool_output: ?array, model: ?string, tokens_used: int}
     */
    private function converse(ChatTools $tools, ChatSession $session): array
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

            $response = $groq->chat($groqMessages, ['tools' => $tools->definitions(), 'tool_choice' => 'auto']);
            $choice = $response['choices'][0]['message'] ?? [];

            $amounts = [];
            $toolName = null;
            $toolInput = null;
            $toolOutput = null;

            if (! empty($choice['tool_calls'])) {
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

                $response = $groq->chat($groqMessages, ['tools' => $tools->definitions(), 'tool_choice' => 'auto']);
                $choice = $response['choices'][0]['message'] ?? [];
            }

            $draft = (string) ($choice['content'] ?? '');
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

    private function currentSession(ChatSetting $settings): ?ChatSession
    {
        if ($this->chatSessionId) {
            return ChatSession::find($this->chatSessionId);
        }

        $ipHash = hash('sha256', request()->ip().config('app.key'));

        $allowed = RateLimiter::attempt(
            "chat-session:{$ipHash}",
            $settings->max_sessions_per_ip_per_hour,
            static fn () => true,
            3600,
        );

        if (! $allowed) {
            return null;
        }

        $session = ChatSession::create([
            'status' => 'open',
            'locale' => app()->getLocale(),
            'page_context' => ['url' => url()->current()],
            'ip_hash' => $ipHash,
            'consent' => true,
        ]);

        $this->chatSessionId = $session->id;

        return $session;
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

    public function render()
    {
        return view('livewire.public.chat-widget');
    }
}
