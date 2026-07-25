<div>
    <div class="mt-6 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Reviewer') }}</th>
                    <th class="px-4 py-3">{{ __('Clinic') }}</th>
                    <th class="px-4 py-3">{{ __('Rating') }}</th>
                    <th class="px-4 py-3">{{ __('Review') }}</th>
                    <th class="px-4 py-3">{{ __('Received') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($reviews as $review)
                    <tr wire:key="review-{{ $review->id }}" class="hover:bg-ink-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink-900">{{ $review->reviewer_name }}</p>
                            <p class="text-xs text-ink-500">
                                {{ $review->reviewerCountry?->name ?? '—' }}
                                @if ($review->is_verified)
                                    &middot; <span class="text-teal-600">{{ __('Verified patient') }}</span>
                                @endif
                            </p>
                        </td>
                        <td class="px-4 py-3 text-ink-600">
                            {{ $review->reviewable?->getTranslation('name', app()->getLocale()) ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ str_repeat('★', $review->rating) }}</td>
                        <td class="px-4 py-3 text-ink-600">
                            @if ($review->title)
                                <p class="font-medium text-ink-800">{{ $review->title }}</p>
                            @endif
                            <p class="max-w-sm truncate" title="{{ $review->body }}">{{ $review->body }}</p>
                        </td>
                        <td class="px-4 py-3 text-ink-500">{{ $review->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <button type="button" wire:click="approve({{ $review->id }})" class="font-medium text-success-600 hover:underline">
                                    {{ __('Approve') }}
                                </button>
                                <button type="button" wire:click="reject({{ $review->id }})" class="font-medium text-danger-500 hover:underline">
                                    {{ __('Reject') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-ink-500">{{ __('No reviews awaiting moderation.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reviews->links() }}
    </div>
</div>
