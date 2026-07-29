<?php

declare(strict_types=1);

use App\Livewire\Public\ChatWidget;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config(['ai.providers.groq.api_key' => 'test-key']);
});

it('hides the widget entirely when the chat assistant is disabled', function () {
    Livewire::test(ChatWidget::class)
        ->assertDontSee('Clinicest AI Assistant');
});

it('creates a lead via the create_lead tool once the model calls it', function () {
    ChatSetting::current()->update(['enabled' => true]);

    Http::fake([
        'https://api.groq.com/*' => Http::sequence()
            ->push([
                'model' => 'llama-3.3-70b-versatile',
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [[
                            'id' => 'call_1',
                            'function' => [
                                'name' => 'create_lead',
                                'arguments' => json_encode([
                                    'full_name' => 'Jane Doe',
                                    'email' => 'jane@example.com',
                                ]),
                            ],
                        ]],
                    ],
                ]],
                'usage' => ['total_tokens' => 120],
            ])
            ->push([
                'model' => 'llama-3.3-70b-versatile',
                'choices' => [[
                    'message' => ['role' => 'assistant', 'content' => 'Teşekkürler Jane, ekibimiz seninle iletişime geçecek!'],
                ]],
                'usage' => ['total_tokens' => 60],
            ]),
    ]);

    Livewire::test(ChatWidget::class)
        ->set('draft', 'Adım Jane Doe, e-postam jane@example.com, beni arayın lütfen.')
        ->call('send')
        ->assertSet('limitReached', false);

    $lead = Lead::where('email', 'jane@example.com')->first();

    expect($lead)->not->toBeNull();
    expect($lead->channel)->toBe('ai_advisor');
    expect(ChatSession::first()->lead_id)->toBe($lead->id);
    expect(ChatSession::first()->status)->toBe('converted');
    expect(ChatMessage::where('role', 'assistant')->first()->content)->toBe('Teşekkürler Jane, ekibimiz seninle iletişime geçecek!');
});

it('replaces the reply with a safe fallback when the model states an unverified price', function () {
    ChatSetting::current()->update(['enabled' => true]);

    Http::fake([
        'https://api.groq.com/*' => Http::response([
            'model' => 'llama-3.3-70b-versatile',
            'choices' => [[
                'message' => ['role' => 'assistant', 'content' => 'Bu tedavi tam olarak €2500 tutar.'],
            ]],
            'usage' => ['total_tokens' => 40],
        ]),
    ]);

    Livewire::test(ChatWidget::class)
        ->set('draft', 'Implant fiyatı ne kadar?')
        ->call('send');

    $assistantMessage = ChatMessage::where('role', 'assistant')->first();

    expect($assistantMessage->flagged)->toBeTrue();
    expect($assistantMessage->content)->not->toContain('2500');
    expect($assistantMessage->original_draft)->toContain('2500');
});

it('stops accepting messages once the per-session cap is reached', function () {
    ChatSetting::current()->update(['enabled' => true, 'max_messages_per_session' => 1]);

    $session = ChatSession::create([
        'status' => 'open',
        'locale' => 'en',
        'ip_hash' => hash('sha256', 'test'),
        'message_count' => 1,
    ]);

    Http::fake();

    Livewire::test(ChatWidget::class)
        ->set('chatSessionId', $session->id)
        ->set('draft', 'One more question please')
        ->call('send')
        ->assertSet('limitReached', true);

    Http::assertNothingSent();
    expect(ChatMessage::count())->toBe(0);
});
