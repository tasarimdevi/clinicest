<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="font-serif text-3xl font-medium text-ink-900">{{ __('Hi :name, here is your request', ['name' => $lead->full_name]) }}</h1>
    <p class="mt-2 text-sm text-ink-500">
        {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? __('General enquiry') }}
        &middot; {{ __('Submitted') }} {{ $lead->created_at->format('d M Y') }}
    </p>

    {{-- Offers --}}
    <div class="mt-8 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="font-serif text-lg font-medium text-ink-900">{{ __('Offers') }}</h2>
        <div class="mt-4 space-y-4">
            @forelse ($offers as $offer)
                <div class="rounded-md border border-ink-100 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-ink-900">{{ $offer->title }}</p>
                            <p class="text-xs text-ink-500">
                                {{ $offer->clinic->getTranslation('name', app()->getLocale()) }}
                                @if ($offer->valid_until)
                                    &middot; {{ __('valid until :date', ['date' => $offer->valid_until->format('d M Y')]) }}
                                @endif
                            </p>
                        </div>
                        <span class="font-mono text-sm font-semibold tabular-nums text-ink-900">
                            {{ $offer->currency }} {{ number_format($offer->price_total / 100, 0) }}
                        </span>
                    </div>

                    @if ($offer->treatment_plan)
                        <p class="mt-2 text-sm text-ink-600">{{ $offer->treatment_plan }}</p>
                    @endif

                    @if (in_array($offer->status->value, ['sent', 'viewed'], true))
                        <div class="mt-3 flex gap-2">
                            <x-button wire:click="acceptOffer({{ $offer->id }})" size="sm">{{ __('Accept') }}</x-button>
                            <x-button wire:click="rejectOffer({{ $offer->id }})" variant="ghost" size="sm">{{ __('Decline') }}</x-button>
                        </div>
                    @else
                        <span class="mt-3 inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                            {{ $offer->status->label() }}
                        </span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-ink-500">{{ __('No offers yet — your matched clinic is preparing your treatment plan.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Appointments --}}
    <div class="mt-6 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="font-serif text-lg font-medium text-ink-900">{{ __('Appointments') }}</h2>
        <div class="mt-4 space-y-4">
            @forelse ($appointments as $appointment)
                <div class="rounded-md border border-ink-100 p-4">
                    <p class="font-medium text-ink-900">{{ $appointment->type->label() }}</p>
                    <p class="text-xs text-ink-500">
                        {{ $appointment->clinic->getTranslation('name', app()->getLocale()) }}
                        &middot; {{ $appointment->scheduled_at->format('d M Y H:i') }} ({{ $appointment->timezone }})
                    </p>

                    @if ($appointment->status->value === 'requested')
                        <div class="mt-3 flex gap-2">
                            <x-button wire:click="confirmAppointment({{ $appointment->id }})" size="sm">{{ __('Confirm') }}</x-button>
                            <x-button wire:click="cancelAppointment({{ $appointment->id }})" variant="ghost" size="sm">{{ __('Cancel') }}</x-button>
                        </div>
                    @else
                        <div class="mt-3 flex items-center gap-3">
                            <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                {{ $appointment->status->label() }}
                            </span>
                            @if ($appointment->status->value === 'confirmed')
                                <button type="button" wire:click="cancelAppointment({{ $appointment->id }})" class="text-xs font-medium text-danger-500 hover:underline">
                                    {{ __('Cancel') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-ink-500">{{ __('No appointments requested yet.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Messages, per clinic --}}
    @if ($acceptedAssignments->isNotEmpty())
        <div class="mt-6 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="font-serif text-lg font-medium text-ink-900">{{ __('Messages') }}</h2>
            <div class="mt-4 space-y-8">
                @foreach ($acceptedAssignments as $assignment)
                    @php $clinicMessages = $messagesByClinic->get($assignment->clinic_id, collect()); @endphp
                    <div>
                        <p class="text-sm font-semibold text-ink-800">{{ $assignment->clinic->getTranslation('name', app()->getLocale()) }}</p>
                        <div class="mt-3 space-y-3">
                            @forelse ($clinicMessages as $message)
                                <div class="flex {{ $message->direction === 'inbound' ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-md rounded-lg px-4 py-2.5 text-sm {{ $message->direction === 'inbound' ? 'bg-brand-700 text-white' : 'bg-ink-100 text-ink-800' }}">
                                        <p>{{ $message->body }}</p>
                                        <p class="mt-1 text-xs {{ $message->direction === 'inbound' ? 'text-white/70' : 'text-ink-500' }}">
                                            {{ $message->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-ink-500">{{ __('No messages yet.') }}</p>
                            @endforelse
                        </div>
                        <form wire:submit="sendMessage({{ $assignment->clinic_id }})" class="mt-3 flex gap-2">
                            <input type="text" wire:model="replyBodies.{{ $assignment->clinic_id }}" placeholder="{{ __('Write a message…') }}" class="flex-1 rounded-md border-ink-300 text-sm">
                            <x-button type="submit" size="sm">{{ __('Send') }}</x-button>
                        </form>
                        @error("replyBodies.{$assignment->clinic_id}") <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Review --}}
    @if ($canReview)
        <div class="mt-6 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="font-serif text-lg font-medium text-ink-900">{{ __('How was your treatment?') }}</h2>
            <p class="mt-1 text-sm text-ink-500">
                {{ __('Your review will be shown as verified — we know you completed treatment with :clinic.', ['clinic' => $treatmentCase->clinic->getTranslation('name', app()->getLocale())]) }}
            </p>

            @if ($reviewSubmitted)
                <div class="mt-4 rounded-md border border-success-500/30 bg-success-500/5 p-4 text-sm text-success-600">
                    {{ __('Thank you — your review has been submitted and will appear once approved.') }}
                </div>
            @else
                <form wire:submit="submitReview" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Rating') }}</label>
                        <select wire:model="reviewRating" class="mt-1.5 w-full max-w-[100px] rounded-md border-ink-300 text-sm">
                            @foreach ([5, 4, 3, 2, 1] as $r)
                                <option value="{{ $r }}">{{ str_repeat('★', $r) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Title (optional)') }}</label>
                        <input type="text" wire:model="reviewTitle" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Your review') }}</label>
                        <textarea wire:model="reviewBody" rows="4" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                        @error('reviewBody') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <x-button type="submit" size="sm">{{ __('Submit review') }}</x-button>
                </form>
            @endif
        </div>
    @endif
</div>
