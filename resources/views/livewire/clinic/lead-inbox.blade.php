<div>
    <div class="overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Patient') }}</th>
                    <th class="px-4 py-3">{{ __('Treatment') }}</th>
                    <th class="px-4 py-3">{{ __('SLA') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($assignments as $assignment)
                    <tr wire:key="assignment-{{ $assignment->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink-900">{{ $assignment->lead->full_name }}</p>
                            <p class="text-xs text-ink-500">{{ $assignment->lead->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-ink-600">
                            {{ $assignment->lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-ink-500">
                            @if ($assignment->status === 'offered' && $assignment->sla_due_at)
                                {{ $assignment->sla_due_at->diffForHumans() }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($assignment->status === 'offered')
                                <div class="flex justify-end gap-2">
                                    <x-button wire:click="accept({{ $assignment->id }})" size="sm">{{ __('Accept') }}</x-button>
                                    <x-button wire:click="decline({{ $assignment->id }})" variant="ghost" size="sm">{{ __('Decline') }}</x-button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-ink-500">{{ __('No leads assigned yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
</div>
