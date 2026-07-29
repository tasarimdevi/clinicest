<div class="max-w-xl space-y-6">
    <div>
        <h2 class="text-sm font-semibold text-ink-900">{{ __('AI Chat Assistant') }}</h2>
        <p class="mt-1 text-xs text-ink-500">
            {{ __('Toggle the public chat widget and tune its abuse/cost caps — changes apply immediately, no redeploy needed.') }}
        </p>
    </div>

    @if ($saved)
        <div class="rounded-lg border border-success-500/30 bg-success-500/10 px-4 py-2 text-sm text-success-600">
            {{ __('Settings saved.') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <label class="flex items-center gap-3">
            <input type="checkbox" wire:model="enabled" class="h-4 w-4 rounded border-ink-300">
            <span class="text-sm font-medium text-ink-900">{{ __('Chat widget enabled on the public site') }}</span>
        </label>

        <div>
            <label class="block text-sm font-medium text-ink-700">{{ __('Daily token budget') }}</label>
            <input type="number" wire:model="daily_budget_tokens" min="1000"
                   class="mt-1 block w-48 rounded-md border-ink-300 text-sm">
            @error('daily_budget_tokens') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink-700">{{ __('Max messages per conversation') }}</label>
            <input type="number" wire:model="max_messages_per_session" min="1" max="100"
                   class="mt-1 block w-48 rounded-md border-ink-300 text-sm">
            @error('max_messages_per_session') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink-700">{{ __('Max new conversations per visitor per hour') }}</label>
            <input type="number" wire:model="max_sessions_per_ip_per_hour" min="1" max="100"
                   class="mt-1 block w-48 rounded-md border-ink-300 text-sm">
            @error('max_sessions_per_ip_per_hour') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
            {{ __('Save') }}
        </button>
    </form>
</div>
