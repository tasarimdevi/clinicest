<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ChatSetting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Kill switch + abuse caps for the AI chat assistant (chat_settings is a
 * deliberate single row — see 2026_07_29_..._create_chat_tables.php). Lets
 * this be tuned without a redeploy, per the brief's cost/abuse-control
 * requirement.
 */
#[Layout('layouts.app', ['title' => 'Chat Assistant'])]
class ChatSettings extends Component
{
    public bool $enabled = false;

    public int $daily_budget_tokens = 200000;

    public int $max_messages_per_session = 20;

    public int $max_sessions_per_ip_per_hour = 10;

    public bool $saved = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);

        $settings = ChatSetting::current();
        $this->enabled = $settings->enabled;
        $this->daily_budget_tokens = $settings->daily_budget_tokens;
        $this->max_messages_per_session = $settings->max_messages_per_session;
        $this->max_sessions_per_ip_per_hour = $settings->max_sessions_per_ip_per_hour;
    }

    protected function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'daily_budget_tokens' => ['required', 'integer', 'min:1000'],
            'max_messages_per_session' => ['required', 'integer', 'min:1', 'max:100'],
            'max_sessions_per_ip_per_hour' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);

        $validated = $this->validate();

        ChatSetting::current()->update($validated);

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.admin.chat-settings');
    }
}
