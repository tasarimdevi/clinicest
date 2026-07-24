<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by name…') }}"
                   class="w-full max-w-xs rounded-md border-ink-300 text-sm">
            <select wire:model.live="clinic" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All clinics') }}</option>
                @foreach ($clinics as $c)
                    <option value="{{ $c->id }}">{{ $c->getTranslation('name', app()->getLocale()) }}</option>
                @endforeach
            </select>
        </div>
        @can('create', \App\Models\Doctor::class)
            <x-button :href="route('admin.doctors.create')" as="a" size="sm">{{ __('Add doctor') }}</x-button>
        @endcan
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Doctor') }}</th>
                    <th class="px-4 py-3">{{ __('Clinic') }}</th>
                    <th class="px-4 py-3">{{ __('Specialty') }}</th>
                    <th class="px-4 py-3">{{ __('Experience') }}</th>
                    <th class="px-4 py-3">{{ __('Rating') }}</th>
                    <th class="px-4 py-3">{{ __('Featured') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($doctors as $doctor)
                    <tr wire:key="doctor-{{ $doctor->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $doctor->full_name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $doctor->clinic?->getTranslation('name', app()->getLocale()) ?? '—' }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $doctor->getTranslation('specialty', app()->getLocale()) ?: '—' }}</td>
                        <td class="px-4 py-3 font-mono tabular-nums text-ink-600">
                            {{ $doctor->years_experience ? $doctor->years_experience.' yrs' : '—' }}
                        </td>
                        <td class="px-4 py-3 font-mono tabular-nums text-ink-700">
                            @if ($doctor->rating_count > 0)
                                ★ {{ number_format($doctor->rating_avg, 1) }} ({{ $doctor->rating_count }})
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleFeatured({{ $doctor->id }})"
                                    @class(['text-xs font-semibold', 'text-gold-600' => $doctor->is_featured, 'text-ink-400' => ! $doctor->is_featured])>
                                {{ $doctor->is_featured ? __('Featured') : __('—') }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="font-medium text-brand-600 hover:underline">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-ink-500">{{ __('No doctors match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $doctors->links() }}
    </div>
</div>
