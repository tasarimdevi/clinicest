<div>
    @if ($enabled)
        <div x-data="{ open: false }">
            <button @click="open = !open" type="button"
                class="fixed bottom-20 right-24 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-brand-700 text-white shadow-raised transition hover:scale-105 sm:bottom-5"
                :aria-label="open ? '{{ __('Close chat') }}' : '{{ __('Chat with AI assistant') }}'">
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition
                 class="fixed inset-x-4 bottom-36 z-40 flex max-h-[70vh] flex-col overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-hero sm:inset-x-auto sm:bottom-20 sm:right-24 sm:w-96">
                <div class="flex items-center justify-between bg-brand-950 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Clinicest AI Assistant') }}</p>
                        <p class="text-xs text-ink-300">{{ __('AI assistant — not a human agent') }}</p>
                    </div>
                    <a href="https://wa.me/" target="_blank" rel="noopener" class="text-xs font-medium text-gold-400 hover:text-gold-300">
                        {{ __('Talk to a human') }}
                    </a>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto p-4" x-ref="scroll"
                     x-init="$watch('$wire.messages', () => $nextTick(() => $refs.scroll.scrollTop = $refs.scroll.scrollHeight))"
                     @if ($waiting) wire:poll.1500ms="poll" @endif>
                    @if (empty($messages))
                        <p class="text-sm text-ink-500">
                            {{ __('Hi! Ask me about treatments, the process, or costs — I can help you get a free quote.') }}
                        </p>
                    @endif

                    @foreach ($messages as $i => $message)
                        <div wire:key="chat-msg-{{ $i }}" @class([
                            'max-w-[85%] rounded-lg px-3 py-2 text-sm',
                            'ml-auto bg-brand-600 text-white' => $message['role'] === 'user',
                            'bg-ink-50 text-ink-800' => $message['role'] === 'assistant',
                        ])>
                            @if ($message['role'] === 'assistant')
                                <span x-data="{ full: @js($message['content']), shown: '' }"
                                      x-init="let n = 0; const iv = setInterval(() => { shown = full.slice(0, n++); if (n > full.length) clearInterval(iv) }, 12)"
                                      x-text="shown"></span>
                            @else
                                {{ $message['content'] }}
                            @endif
                        </div>
                    @endforeach

                    @if ($waiting)
                        <div class="flex gap-1 rounded-lg bg-ink-50 px-3 py-2" style="width: fit-content" wire:loading.class="opacity-60" wire:target="poll">
                            <span class="h-2 w-2 animate-bounce rounded-full bg-ink-300"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-ink-300 [animation-delay:0.15s]"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-ink-300 [animation-delay:0.3s]"></span>
                        </div>
                    @endif

                    @if ($limitReached)
                        <p class="rounded-lg bg-gold-50 px-3 py-2 text-xs text-ink-600">
                            {{ __('This conversation has reached its limit for now — please use the') }}
                            <a href="{{ route('get-quote') }}" class="underline">{{ __('nav.get_quote') }}</a>
                            {{ __('form and our team will follow up directly.') }}
                        </p>
                    @endif
                </div>

                <form wire:submit="send" class="flex items-center gap-2 border-t border-ink-100 p-3">
                    <input type="text" wire:model="draft" @disabled($limitReached || $waiting)
                           class="flex-1 rounded-md border border-ink-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none disabled:bg-ink-50"
                           placeholder="{{ __('Type your question…') }}">
                    <button type="submit" wire:loading.attr="disabled" wire:target="send" @disabled($limitReached || $waiting)
                            class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white disabled:opacity-50">
                        {{ __('Send') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
