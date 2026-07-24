<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search name or email…') }}"
                   class="w-full max-w-xs rounded-md border-ink-300 text-sm">
            <select wire:model.live="status" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Patient') }}</th>
                    <th class="px-4 py-3">{{ __('Treatment') }}</th>
                    <th class="px-4 py-3">{{ __('Country') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Received') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($leads as $lead)
                    <tr wire:key="lead-{{ $lead->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink-900">{{ $lead->full_name }}</p>
                            <p class="text-xs text-ink-500">{{ $lead->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-ink-600">
                            {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $lead->country?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                {{ $lead->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-ink-500">{{ $lead->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="font-medium text-brand-700 hover:underline">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-ink-500">{{ __('No leads match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $leads->links() }}
    </div>
</div>
