<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Appointments with :name', ['name' => $lead->full_name]) }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? __('No treatment specified') }}
            &middot; {{ $lead->email }}
        </p>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Request a new appointment') }}</h2>
        <form wire:submit="request" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Type') }}</label>
                <select wire:model="type" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Doctor (optional)') }}</label>
                <select wire:model="doctor_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Date & time') }}</label>
                <input type="datetime-local" wire:model="scheduled_at" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                @error('scheduled_at') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Timezone') }}</label>
                <input type="text" wire:model="timezone" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                @error('timezone') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
            @if ($type === 'remote_consult')
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Meeting link') }}</label>
                    <input type="text" wire:model="meeting_url" placeholder="https://" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('meeting_url') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
            @endif
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-ink-700">{{ __('Notes (optional)') }}</label>
                <textarea wire:model="notes" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                @error('notes') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <x-button type="submit" size="lg" wire:loading.attr="disabled" wire:target="request">
                    <span wire:loading.remove wire:target="request">{{ __('Request appointment') }}</span>
                    <span wire:loading wire:target="request">{{ __('Sending…') }}</span>
                </x-button>
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Scheduled appointments') }}</h2>
        <ul class="mt-4 space-y-4">
            @forelse ($appointments as $appointment)
                <li class="rounded-md border border-ink-100 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-ink-900">{{ $appointment->type->label() }}</p>
                            <p class="text-xs text-ink-500">
                                {{ $appointment->scheduled_at->format('d M Y H:i') }} ({{ $appointment->timezone }})
                                @if ($appointment->meeting_url)
                                    &middot; <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener" class="text-brand-700 hover:underline">{{ __('Meeting link') }}</a>
                                @endif
                            </p>
                            @if ($appointment->notes)
                                <p class="mt-1 text-xs text-ink-500">{{ $appointment->notes }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($statuses as $s)
                            <button wire:click="updateStatus({{ $appointment->id }}, '{{ $s->value }}')"
                                    @class([
                                        'rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-brand-700 text-white' => $appointment->status === $s,
                                        'bg-ink-100 text-ink-600 hover:bg-ink-200' => $appointment->status !== $s,
                                    ])>
                                {{ $s->label() }}
                            </button>
                        @endforeach
                    </div>
                </li>
            @empty
                <li class="text-sm text-ink-500">{{ __('No appointments requested yet.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
