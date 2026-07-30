<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Jobs\ProcessChatReply;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Lead-conversion chat widget (docs/07-ai-architecture.md §2.4). One
 * ChatSession per page load — kept deliberately simple, no cross-page-load
 * resume via cookie/localStorage, since a fresh session per visit is enough
 * for the "guide toward /get-quote" goal this was built for.
 *
 * send() only validates, checks limits, saves the user's message, and
 * dispatches ProcessChatReply — it does NOT call Groq inline. clinicest-app
 * is Apache prefork, so a synchronous request that sits waiting on a slow
 * Groq round-trip (or several, for chained tool calls) ties up a whole OS
 * worker process on a host with very little memory headroom; queuing the
 * actual conversation turn means this request returns almost immediately,
 * and the widget polls (see chat-widget.blade.php) until the reply lands.
 *
 * Never streams raw model tokens either way: ChatResponseVerifier needs the
 * complete response to check numeric claims before anything reaches the
 * user, so the "typing" effect is a client-side reveal of an
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

    public bool $waiting = false;

    // Must be public: poll() runs in a separate request from send(), and
    // only public properties round-trip through Livewire's state between
    // requests.
    public ?int $lastSeenMessageId = null;

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

    public function send(): void
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

        $userMessage = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $userText,
        ]);

        $this->messages[] = ['role' => 'user', 'content' => $userText];
        $this->lastSeenMessageId = $userMessage->id;
        $this->waiting = true;

        ProcessChatReply::dispatch($session->id);
    }

    /**
     * Polled from the widget (see chat-widget.blade.php's conditional
     * wire:poll) while waiting on ProcessChatReply. A plain indexed lookup,
     * not expensive to run every couple of seconds while a reply is pending.
     */
    public function poll(): void
    {
        if (! $this->waiting || ! $this->chatSessionId) {
            return;
        }

        $reply = ChatMessage::where('chat_session_id', $this->chatSessionId)
            ->where('role', 'assistant')
            ->where('id', '>', $this->lastSeenMessageId ?? 0)
            ->orderBy('id')
            ->first();

        if ($reply === null) {
            return;
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $reply->content];
        $this->lastSeenMessageId = $reply->id;
        $this->waiting = false;
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

    public function render()
    {
        return view('livewire.public.chat-widget');
    }
}
