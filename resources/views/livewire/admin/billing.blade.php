<div class="space-y-8">
    @php $canManage = auth()->user()->can('billing.manage'); @endphp

    {{-- Subscriptions --}}
    <div>
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Clinic subscriptions') }}</h2>
        <p class="mt-1 text-xs text-ink-500">{{ __('Assigning a plan is recorded immediately — no payment is processed (real billing is not wired yet).') }}</p>

        <div class="mt-4 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
            <table class="min-w-full divide-y divide-ink-100 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Clinic') }}</th>
                        <th class="px-4 py-3">{{ __('Current plan') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        @if ($canManage)<th class="px-4 py-3">{{ __('Assign plan') }}</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($clinics as $clinic)
                        <tr wire:key="sub-{{ $clinic->id }}">
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $clinic->getTranslation('name', 'en') }}</td>
                            <td class="px-4 py-3 text-ink-600">
                                {{ $clinic->activeSubscription?->plan?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($clinic->activeSubscription)
                                    <span class="inline-flex rounded-full bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600">
                                        {{ $clinic->activeSubscription->status->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-ink-400">{{ __('No subscription') }}</span>
                                @endif
                            </td>
                            @if ($canManage)
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <select wire:model="planFor.{{ $clinic->id }}" class="rounded-md border-ink-300 text-xs">
                                            <option value="">{{ __('Select…') }}</option>
                                            @foreach ($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->currency }} {{ number_format($plan->price_month / 100, 0) }}/mo)</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="assignPlan({{ $clinic->id }})" class="text-xs font-medium text-brand-700 hover:underline">
                                            {{ __('Assign') }}
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Invoices --}}
    <div>
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Invoices') }}</h2>

        <div class="mt-4 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
            <table class="min-w-full divide-y divide-ink-100 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Number') }}</th>
                        <th class="px-4 py-3">{{ __('Clinic') }}</th>
                        <th class="px-4 py-3">{{ __('For') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Total') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Issued') }}</th>
                        @if ($canManage)<th class="px-4 py-3"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($invoices as $invoice)
                        <tr wire:key="inv-{{ $invoice->id }}" class="hover:bg-ink-50">
                            <td class="px-4 py-3 font-mono text-xs text-ink-700">{{ $invoice->number }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $invoice->clinic?->getTranslation('name', 'en') }}</td>
                            <td class="px-4 py-3 text-ink-500">{{ class_basename($invoice->billable_type) ?: '—' }}</td>
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
                            @if ($canManage)
                                <td class="px-4 py-3 text-right">
                                    @if ($invoice->status->isPayable())
                                        <button type="button" wire:click="markPaid({{ $invoice->id }})" class="text-xs font-medium text-success-600 hover:underline">
                                            {{ __('Mark paid') }}
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-ink-500">{{ __('No invoices yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
</div>
