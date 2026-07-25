<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by name…') }}"
                   class="w-full max-w-xs rounded-md border-ink-300 text-sm">
            <select wire:model.live="status" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="published">{{ __('Published') }}</option>
            </select>
            <select wire:model.live="category" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->getTranslation('name', app()->getLocale()) }}</option>
                @endforeach
            </select>
        </div>
        @can('create', \App\Models\Treatment::class)
            <x-button :href="route('admin.treatments.create')" as="a" size="sm">{{ __('Add treatment') }}</x-button>
        @endcan
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('From') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($treatments as $treatment)
                    <tr wire:key="treatment-{{ $treatment->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">
                            {{ $treatment->getTranslation('name', app()->getLocale()) }}
                            @if ($treatment->is_featured)
                                <span class="ml-1 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-medium text-gold-600">{{ __('Featured') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $treatment->category?->getTranslation('name', app()->getLocale()) ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-ink-700">
                            @if ($treatment->base_price_min)
                                {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @can('publish', $treatment)
                                <button wire:click="togglePublish({{ $treatment->id }})"
                                        @class(['text-xs font-semibold', 'text-success-600' => $treatment->status === 'published', 'text-ink-400' => $treatment->status !== 'published'])>
                                    {{ $treatment->status === 'published' ? __('Published') : __('Draft') }}
                                </button>
                            @else
                                <span class="text-xs font-semibold text-ink-500">{{ $treatment->status === 'published' ? __('Published') : __('Draft') }}</span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.treatments.edit', $treatment) }}" class="font-medium text-brand-600 hover:underline">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-ink-500">{{ __('No treatments match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $treatments->links() }}
    </div>
</div>
