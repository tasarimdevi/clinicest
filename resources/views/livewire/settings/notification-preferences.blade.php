<div class="max-w-2xl">
    @if (session('saved'))
        <div class="mb-6 rounded-md border border-success-500/30 bg-success-500/5 px-4 py-3 text-sm text-success-600">
            {{ __('Preferences saved.') }}
        </div>
    @endif

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Email notifications') }}</h2>
        <p class="mt-1 text-xs text-ink-500">
            {{ __('In-app notifications (the bell icon) are always recorded. These control what also gets emailed to you.') }}
        </p>

        <div class="mt-4 divide-y divide-ink-100">
            @foreach ($types as $class => $label)
                <label class="flex items-center justify-between gap-4 py-3 text-sm">
                    <span class="text-ink-700">{{ __($label) }}</span>
                    <input type="checkbox" wire:model="mail.{{ $class }}" class="rounded border-ink-300 text-brand-600">
                </label>
            @endforeach
        </div>

        <div class="mt-4 border-t border-ink-200 pt-4">
            <label class="flex items-center justify-between gap-4 text-sm">
                <span>
                    <span class="font-medium text-ink-900">{{ __('Daily digest email') }}</span>
                    <span class="block text-xs text-ink-500">{{ __('A daily summary of anything unread in your notification bell.') }}</span>
                </span>
                <input type="checkbox" wire:model="digest" class="rounded border-ink-300 text-brand-600">
            </label>
        </div>

        <x-button wire:click="save" class="mt-6">{{ __('Save preferences') }}</x-button>
    </div>
</div>
