<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        {{-- Patient / request --}}
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink-900">{{ $lead->full_name }}</h2>
                    <p class="text-sm text-ink-500">{{ $lead->email }} @if($lead->whatsapp) &middot; {{ $lead->whatsapp }} @endif</p>
                </div>
                <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                    {{ $lead->status->label() }}
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-ink-500">{{ __('Treatment') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">
                        {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-ink-500">{{ __('Country') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ $lead->country?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-ink-500">{{ __('Channel') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ ucfirst($lead->channel) }}</dd>
                </div>
                <div>
                    <dt class="text-ink-500">{{ __('Budget') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">
                        @if ($lead->budget_min || $lead->budget_max)
                            {{ $lead->currency }} {{ number_format(($lead->budget_min ?? 0) / 100) }}–{{ number_format(($lead->budget_max ?? 0) / 100) }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-ink-500">{{ __('Timeline') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ $lead->timeline ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-ink-500">{{ __('Received') }}</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ $lead->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>

            @if ($lead->message)
                <div class="mt-5 rounded-md bg-ink-50 p-4 text-sm text-ink-700">
                    {{ $lead->message }}
                </div>
            @endif
        </div>

        {{-- Offers --}}
        @can('viewAny', \App\Models\Offer::class)
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-ink-900">{{ __('Offers') }}</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($offers as $offer)
                        <li class="rounded-md border border-ink-100 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-ink-900">{{ $offer->title }}</p>
                                    <p class="text-xs text-ink-500">
                                        {{ $offer->clinic->getTranslation('name', app()->getLocale()) }}
                                        &middot; {{ $offer->created_at->format('d M Y H:i') }}
                                        @if ($offer->valid_until)
                                            &middot; {{ __('valid until :date', ['date' => $offer->valid_until->format('d M Y')]) }}
                                        @endif
                                    </p>
                                </div>
                                <span class="font-mono text-sm font-semibold tabular-nums text-ink-900">
                                    {{ $offer->currency }} {{ number_format($offer->price_total / 100, 0) }}
                                </span>
                            </div>

                            @can('update', $offer)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($offerStatuses as $s)
                                        <button wire:click="updateOfferStatus({{ $offer->id }}, '{{ $s->value }}')"
                                                @class([
                                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-brand-700 text-white' => $offer->status === $s,
                                                    'bg-ink-100 text-ink-600 hover:bg-ink-200' => $offer->status !== $s,
                                                ])>
                                            {{ $s->label() }}
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <span class="mt-3 inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                    {{ $offer->status->label() }}
                                </span>
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-ink-500">{{ __('No offers sent yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        @endcan

        {{-- Appointments --}}
        @can('viewAny', \App\Models\Appointment::class)
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-ink-900">{{ __('Appointments') }}</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($appointments as $appointment)
                        <li class="rounded-md border border-ink-100 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-ink-900">{{ $appointment->type->label() }}</p>
                                    <p class="text-xs text-ink-500">
                                        {{ $appointment->clinic->getTranslation('name', app()->getLocale()) }}
                                        &middot; {{ $appointment->scheduled_at->format('d M Y H:i') }} ({{ $appointment->timezone }})
                                    </p>
                                </div>
                            </div>

                            @can('update', $appointment)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($appointmentStatuses as $s)
                                        <button wire:click="updateAppointmentStatus({{ $appointment->id }}, '{{ $s->value }}')"
                                                @class([
                                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-brand-700 text-white' => $appointment->status === $s,
                                                    'bg-ink-100 text-ink-600 hover:bg-ink-200' => $appointment->status !== $s,
                                                ])>
                                            {{ $s->label() }}
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <span class="mt-3 inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                    {{ $appointment->status->label() }}
                                </span>
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-ink-500">{{ __('No appointments requested yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        @endcan

        {{-- Activity timeline --}}
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h3 class="text-sm font-semibold text-ink-900">{{ __('Activity') }}</h3>
            <ul class="mt-4 space-y-3">
                @forelse ($activities as $activity)
                    <li class="flex gap-3 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                        <div>
                            <p class="text-ink-800">{{ str_replace('_', ' ', $activity->type) }}</p>
                            <p class="text-xs text-ink-500">{{ $activity->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-ink-500">{{ __('No activity yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Status --}}
        @can('update', $lead)
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-ink-900">{{ __('Status') }}</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($statuses as $s)
                        <button wire:click="updateStatus('{{ $s->value }}')"
                                @class([
                                    'rounded-full px-3 py-1.5 text-xs font-medium',
                                    'bg-brand-700 text-white' => $lead->status === $s,
                                    'bg-ink-100 text-ink-600 hover:bg-ink-200' => $lead->status !== $s,
                                ])>
                            {{ $s->label() }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endcan

        {{-- Assignment --}}
        @can('assign', $lead)
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h3 class="text-sm font-semibold text-ink-900">{{ __('Assign to clinics') }}</h3>
                <div class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                    @foreach ($clinics as $clinic)
                        <label class="flex items-center gap-2 text-sm text-ink-700">
                            <input type="checkbox" wire:model="selectedClinicIds" value="{{ $clinic->id }}" class="rounded border-ink-300">
                            {{ $clinic->getTranslation('name', app()->getLocale()) }}
                        </label>
                    @endforeach
                </div>
                @error('selectedClinicIds') <p class="mt-2 text-xs text-danger-500">{{ $message }}</p> @enderror
                <x-button wire:click="assign" size="sm" class="mt-4 w-full">{{ __('Assign') }}</x-button>

                @if ($assignments->isNotEmpty())
                    <ul class="mt-4 space-y-2 border-t border-ink-100 pt-4 text-sm">
                        @foreach ($assignments as $assignment)
                            <li class="flex items-center justify-between">
                                <span class="text-ink-700">{{ $assignment->clinic->getTranslation('name', app()->getLocale()) }}</span>
                                <span class="text-xs text-ink-500">{{ $assignment->status }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endcan
    </div>
</div>
