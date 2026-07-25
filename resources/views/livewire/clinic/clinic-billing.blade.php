<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Billing') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('Your current plan and invoices. To change your plan, contact the Clinicest team.') }}</p>
    </div>

    {{-- Current plan --}}
    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Current plan') }}</h2>
        @if ($subscription)
            <div class="mt-3 flex flex-wrap items-baseline gap-3">
                <span class="font-serif text-xl font-medium text-ink-900">{{ $subscription->plan->name }}</span>
                <span class="inline-flex rounded-full bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600">{{ $subscription->status->label() }}</span>
                <span class="font-mono text-sm tabular-nums text-ink-600">
                    {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price_month / 100, 0) }}/{{ __('mo') }}
                </span>
            </div>
            @if ($subscription->renews_at)
                <p class="mt-1 text-xs text-ink-500">{{ __('Renews') }} {{ $subscription->renews_at->format('d M Y') }}</p>
            @endif
        @else
            <p class="mt-3 text-sm text-ink-500">{{ __('No active subscription.') }}</p>
        @endif
    </div>

    {{-- Available plans --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ($plans as $plan)
            <div @class([
                'rounded-lg border p-5 shadow-card',
                'border-brand-500 bg-brand-50/40' => $subscription && $subscription->plan_id === $plan->id,
                'border-ink-200 bg-white' => ! ($subscription && $subscription->plan_id === $plan->id),
            ])>
                <p class="font-serif text-lg font-medium text-ink-900">{{ $plan->name }}</p>
                <p class="mt-1 font-mono text-sm tabular-nums text-ink-700">
                    {{ $plan->currency }} {{ number_format($plan->price_month / 100, 0) }}<span class="text-ink-400">/{{ __('mo') }}</span>
                </p>
                @if (! empty($plan->features_json))
                    <ul class="mt-3 space-y-1 text-xs text-ink-600">
                        @foreach ($plan->features_json as $feature)
                            <li class="flex gap-1.5"><span class="text-brand-600">✓</span> {{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($subscription && $subscription->plan_id === $plan->id)
                    <p class="mt-3 text-xs font-semibold text-brand-700">{{ __('Your current plan') }}</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Invoices --}}
    <div>
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Invoices') }}</h2>
        <div class="mt-3 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
            <table class="min-w-full divide-y divide-ink-100 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Number') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Total') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Issued') }}</th>
                        <th class="px-4 py-3">{{ __('Due') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($invoices as $invoice)
                        <tr wire:key="ci-{{ $invoice->id }}">
                            <td class="px-4 py-3 font-mono text-xs text-ink-700">{{ $invoice->number }}</td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums text-ink-900">
                                {{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-success-500/10 text-success-600' => $invoice->status->value === 'paid',
                                    'bg-gold-500/10 text-gold-600' => in_array($invoice->status->value, ['sent', 'overdue'], true),
                                    'bg-ink-100 text-ink-600' => in_array($invoice->status->value, ['draft', 'void'], true),
                                ])>{{ $invoice->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3 text-ink-500">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-500">{{ $invoice->due_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-ink-500">{{ __('No invoices yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
