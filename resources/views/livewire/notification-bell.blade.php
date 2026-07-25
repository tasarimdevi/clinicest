<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button @click="open = ! open" type="button" class="relative text-ink-500 hover:text-ink-700" aria-label="{{ __('Notifications') }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-500 px-1 text-[10px] font-semibold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-ink-200 bg-white shadow-raised">
        <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3">
            <span class="text-sm font-semibold text-ink-900">{{ __('Notifications') }}</span>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs font-medium text-brand-700 hover:underline">
                    {{ __('Mark all read') }}
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}"
                   wire:click="markAsRead('{{ $notification->id }}')"
                   class="block border-b border-ink-50 px-4 py-3 text-sm hover:bg-ink-50 {{ $notification->read_at ? '' : 'bg-brand-50/50' }}">
                    <div class="flex items-start gap-2">
                        @unless ($notification->read_at)
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-600"></span>
                        @endunless
                        <div class="{{ $notification->read_at ? 'ml-3.5' : '' }}">
                            <p class="font-medium text-ink-900">{{ $notification->data['title'] ?? '' }}</p>
                            <p class="mt-0.5 text-xs text-ink-500">{{ $notification->data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-ink-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="px-4 py-6 text-center text-sm text-ink-500">{{ __('No notifications yet.') }}</p>
            @endforelse
        </div>
    </div>
</div>
