<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Messages with :name', ['name' => $lead->full_name]) }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ $lead->email }}</p>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <div class="space-y-4">
            @forelse ($messages as $message)
                <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-md rounded-lg px-4 py-2.5 text-sm {{ $message->direction === 'outbound' ? 'bg-brand-700 text-white' : 'bg-ink-100 text-ink-800' }}">
                        <p>{{ $message->body }}</p>
                        <p class="mt-1 text-xs {{ $message->direction === 'outbound' ? 'text-white/70' : 'text-ink-500' }}">
                            {{ $message->direction === 'outbound' ? __('Sent') : __('Logged') }}
                            &middot; {{ ucfirst($message->channel) }}
                            &middot; {{ $message->created_at->format('d M Y H:i') }}
                            @if ($message->sender)
                                {{-- Sender is a User (staff) or a Lead (patient, via the portal) --}}
                                &middot; {{ $message->sender->name ?? $message->sender->full_name ?? '' }}
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-500">{{ __('No messages yet.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <form wire:submit="sendReply" class="space-y-3">
            <label class="block text-sm font-medium text-ink-700">{{ __('Send a reply') }}</label>
            <textarea wire:model="reply_body" rows="3" placeholder="{{ __('This is emailed directly to the patient.') }}" class="w-full rounded-md border-ink-300 text-sm"></textarea>
            @error('reply_body') <p class="text-xs text-danger-500">{{ $message }}</p> @enderror
            <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="sendReply">
                <span wire:loading.remove wire:target="sendReply">{{ __('Send') }}</span>
                <span wire:loading wire:target="sendReply">{{ __('Sending…') }}</span>
            </x-button>
        </form>
    </div>

    <div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-6">
        @if (! $logging)
            <button type="button" wire:click="$set('logging', true)" class="text-sm font-medium text-brand-700 hover:underline">
                {{ __('+ Log a message from another channel') }}
            </button>
        @else
            <p class="text-sm font-medium text-ink-700">{{ __('Log a message') }}</p>
            <p class="mt-1 text-xs text-ink-500">{{ __('For a conversation that happened by phone, WhatsApp, or an email reply outside this system.') }}</p>
            <form wire:submit="logMessage" class="mt-3 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Channel') }}</label>
                        <select wire:model="log_channel" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="whatsapp">{{ __('WhatsApp') }}</option>
                            <option value="call">{{ __('Phone call') }}</option>
                            <option value="email">{{ __('Email') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Direction') }}</label>
                        <select wire:model="log_direction" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="inbound">{{ __('Patient said this to us') }}</option>
                            <option value="outbound">{{ __('We said this to the patient') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('What was said') }}</label>
                    <textarea wire:model="log_body" rows="2" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                    @error('log_body') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <x-button type="submit" size="sm">{{ __('Log it') }}</x-button>
                    <x-button type="button" wire:click="$set('logging', false)" variant="ghost" size="sm">{{ __('Cancel') }}</x-button>
                </div>
            </form>
        @endif
    </div>
</div>
