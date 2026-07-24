<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by name…') }}"
                   class="w-full max-w-xs rounded-md border-ink-300 text-sm">
            <select wire:model.live="tier" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All tiers') }}</option>
                @foreach ($tiers as $t)
                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="city" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All cities') }}</option>
                @foreach ($cities as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        @can('create', \App\Models\Clinic::class)
            <x-button :href="route('admin.clinics.create')" as="a" size="sm">{{ __('Add clinic') }}</x-button>
        @endcan
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Clinic') }}</th>
                    <th class="px-4 py-3">{{ __('City') }}</th>
                    <th class="px-4 py-3">{{ __('Verification') }}</th>
                    <th class="px-4 py-3">{{ __('Rating') }}</th>
                    <th class="px-4 py-3">{{ __('Active') }}</th>
                    <th class="px-4 py-3">{{ __('Featured') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($clinics as $clinic)
                    <tr wire:key="clinic-{{ $clinic->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">
                            {{ $clinic->getTranslation('name', app()->getLocale()) }}
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $clinic->city?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-verification-badge :tier="$clinic->verification_tier->value" />
                        </td>
                        <td class="px-4 py-3 font-mono tabular-nums text-ink-700">
                            @if ($clinic->rating_count > 0)
                                ★ {{ number_format($clinic->rating_avg, 1) }} ({{ $clinic->rating_count }})
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $clinic->id }})"
                                    @class(['text-xs font-semibold', 'text-success-600' => $clinic->is_active, 'text-ink-400' => ! $clinic->is_active])>
                                {{ $clinic->is_active ? __('Active') : __('Inactive') }}
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleFeatured({{ $clinic->id }})"
                                    @class(['text-xs font-semibold', 'text-gold-600' => $clinic->is_featured, 'text-ink-400' => ! $clinic->is_featured])>
                                {{ $clinic->is_featured ? __('Featured') : __('—') }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.clinics.edit', $clinic) }}" class="font-medium text-brand-600 hover:underline">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-ink-500">{{ __('No clinics match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clinics->links() }}
    </div>
</div>
