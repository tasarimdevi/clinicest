<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Ai\ChatConversationService;
use App\Ai\ChatTools;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Runs the Groq conversation turn on the queue worker instead of inline in
 * the web request. clinicest-app is Apache prefork: a synchronous request
 * holding a worker for the several seconds a Groq round-trip (or several,
 * for chained tool calls) takes ties up a real OS process + its memory on a
 * host with very little headroom to spare — queuing it means ChatWidget::
 * send() returns almost immediately and the widget polls for the reply
 * instead (see chat-widget.blade.php).
 */
class ProcessChatReply implements ShouldQueue
{
    use Queueable;

    // Hard cap slightly above ChatConversationService::MAX_TOOL_ROUNDS x
    // GroqProvider's per-call timeout (3 x 12s = 36s), so a stuck call can't
    // hold a queue worker slot indefinitely.
    public int $timeout = 45;

    public int $tries = 1;

    public function __construct(public readonly int $chatSessionId) {}

    public function handle(ChatTools $tools, ChatConversationService $conversation): void
    {
        $session = ChatSession::find($this->chatSessionId);

        if ($session === null) {
            return;
        }

        // SetLocale middleware (app/Http/Middleware/SetLocale.php) is what
        // normally makes route('treatments.show', $model) and
        // app()->getLocale() resolve to the visitor's locale -- it never
        // runs for a queued job, so without this, tools generating a
        // locale-prefixed URL throw ("Missing required parameter: locale")
        // and translations fall back to the app's default locale instead of
        // the visitor's.
        app()->setLocale($session->locale);
        URL::defaults(['locale' => $session->locale]);

        $settings = ChatSetting::current();
        $start = microtime(true);
        $outcome = $conversation->converse($tools, $session);

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
            'flag_reason' => $outcome['flag_reason'] !== null ? Str::limit($outcome['flag_reason'], 250, '') : null,
        ]);

        $session->increment('message_count');
        $session->increment('token_count', $outcome['tokens_used']);
        $settings->recordTokensUsed($outcome['tokens_used']);
    }
}
