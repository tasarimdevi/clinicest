<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap gap-3">
            <select wire:model.live="scope" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All FAQs') }}</option>
                <option value="global">{{ __('Global') }}</option>
                <option value="treatment">{{ __('Treatment-scoped') }}</option>
            </select>
            <select wire:model.live="status" class="rounded-md border-ink-300 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="published">{{ __('Published') }}</option>
            </select>
        </div>
        @can('create', \App\Models\Faq::class)
            <x-button :href="route('admin.faqs.create')" as="a" size="sm">{{ __('Add FAQ') }}</x-button>
        @endcan
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Question') }}</th>
                    <th class="px-4 py-3">{{ __('Scope') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($faqs as $faq)
                    <tr wire:key="faq-{{ $faq->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">{{ Str::limit($faq->getTranslation('question', app()->getLocale()), 80) }}</td>
                        <td class="px-4 py-3 text-ink-600">
                            @if ($faq->faqable)
                                {{ class_basename($faq->faqable_type) }}: {{ $faq->faqable->getTranslation('name', app()->getLocale()) ?? '#'.$faq->faqable_id }}
                            @else
                                {{ __('Global') }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @can('publish', $faq)
                                <button wire:click="togglePublish({{ $faq->id }})"
                                        @class(['text-xs font-semibold', 'text-success-600' => $faq->status === 'published', 'text-ink-400' => $faq->status !== 'published'])>
                                    {{ $faq->status === 'published' ? __('Published') : __('Draft') }}
                                </button>
                            @else
                                <span class="text-xs font-semibold text-ink-500">{{ $faq->status === 'published' ? __('Published') : __('Draft') }}</span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="font-medium text-brand-600 hover:underline">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-ink-500">{{ __('No FAQs match these filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $faqs->links() }}
    </div>
</div>
