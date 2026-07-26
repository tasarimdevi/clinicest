<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-ink-900">{{ __('Analytics') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('Your clinic\'s performance on Clinicest.') }}</p>
        </div>
        <select wire:model.live="range" class="rounded-md border-ink-300 text-sm">
            <option value="30">{{ __('Last 30 days') }}</option>
            <option value="90">{{ __('Last 90 days') }}</option>
            <option value="all">{{ __('All time') }}</option>
        </select>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        @php
            $cards = [
                ['label' => __('Leads received'), 'value' => number_format($kpis['leads']), 'sub' => null],
                ['label' => __('Acceptance rate'), 'value' => $kpis['acceptanceRate'] !== null ? $kpis['acceptanceRate'].'%' : '—', 'sub' => __('of responded leads')],
                ['label' => __('Offer acceptance'), 'value' => $kpis['offerRate'] !== null ? $kpis['offerRate'].'%' : '—', 'sub' => __('of offers sent')],
                ['label' => __('Cases completed'), 'value' => number_format($kpis['completedCases']), 'sub' => null],
                ['label' => __('Revenue'), 'value' => $currency.' '.number_format($kpis['revenue'] / 100, 0), 'sub' => __('completed cases')],
                ['label' => __('Avg. response time'), 'value' => $kpis['avgResponseHours'] !== null ? $kpis['avgResponseHours'].' '.__('h') : '—', 'sub' => __('assigned to reply')],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-lg border border-ink-200 bg-white p-4 shadow-card">
                <p class="text-xs font-medium uppercase tracking-wide text-ink-400">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-mono text-2xl font-semibold tabular-nums text-ink-900">{{ $card['value'] }}</p>
                @if ($card['sub'])<p class="mt-0.5 text-xs text-ink-400">{{ $card['sub'] }}</p>@endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Conversion funnel --}}
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Conversion funnel') }}</h2>
            @php $top = max(1, $funnel[0]['value']); @endphp
            <div class="mt-4 space-y-3">
                @foreach ($funnel as $step)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-700">{{ $step['label'] }}</span>
                            <span class="font-mono font-medium tabular-nums text-ink-900">
                                {{ number_format($step['value']) }}
                                @if (! $loop->first && $funnel[0]['value'] > 0)
                                    <span class="text-ink-400">({{ round($step['value'] / $funnel[0]['value'] * 100) }}%)</span>
                                @endif
                            </span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-ink-100">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($step['value'] / $top * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status breakdowns --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Offers by status') }}</h2>
                <dl class="mt-3 space-y-1.5 text-sm">
                    @forelse ($offers as $status => $count)
                        <div class="flex justify-between"><dt class="capitalize text-ink-600">{{ $status }}</dt><dd class="font-mono tabular-nums text-ink-900">{{ $count }}</dd></div>
                    @empty
                        <p class="text-sm text-ink-500">{{ __('No offers in this period.') }}</p>
                    @endforelse
                </dl>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Appointments by status') }}</h2>
                <dl class="mt-3 space-y-1.5 text-sm">
                    @forelse ($appointments as $status => $count)
                        <div class="flex justify-between"><dt class="capitalize text-ink-600">{{ str_replace('_', ' ', $status) }}</dt><dd class="font-mono tabular-nums text-ink-900">{{ $count }}</dd></div>
                    @empty
                        <p class="text-sm text-ink-500">{{ __('No appointments in this period.') }}</p>
                    @endforelse
                </dl>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Commissions (running, all-time) --}}
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Commissions') }}</h2>
            <p class="mt-1 text-xs text-ink-500">{{ __('Running totals, all time.') }}</p>
            <dl class="mt-3 space-y-1.5 text-sm">
                @forelse ($commissions as $status => $total)
                    <div class="flex justify-between">
                        <dt class="capitalize text-ink-600">{{ $status }}</dt>
                        <dd class="font-mono tabular-nums text-ink-900">{{ $currency }} {{ number_format($total / 100, 0) }}</dd>
                    </div>
                @empty
                    <p class="text-sm text-ink-500">{{ __('No commissions yet.') }}</p>
                @endforelse
            </dl>
        </div>

        {{-- Reviews (all-time) --}}
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Reviews') }}</h2>
            @if ($reviews['count'] > 0)
                <p class="mt-3 font-mono text-2xl font-semibold tabular-nums text-gold-600">★ {{ number_format($reviews['avg'], 1) }}</p>
                <p class="mt-0.5 text-xs text-ink-400">{{ $reviews['count'] }} {{ __('reviews') }}</p>
            @else
                <p class="mt-3 text-sm text-ink-500">{{ __('No reviews yet.') }}</p>
            @endif
        </div>
    </div>
</div>
