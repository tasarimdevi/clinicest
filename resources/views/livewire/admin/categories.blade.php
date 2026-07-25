<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    @php
        $locales = config('clinicest.locales.supported', ['en']);
    @endphp

    {{-- Treatment categories --}}
    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Treatment categories') }}</h2>

        <div class="mt-4 divide-y divide-ink-100">
            @forelse ($treatmentCategories as $category)
                <div wire:key="tc-{{ $category->id }}" class="py-3">
                    <div class="flex items-center gap-2">
                        @foreach ($locales as $loc)
                            <input type="text" wire:model="treatmentNames.{{ $category->id }}.{{ $loc }}" @disabled(! $canEdit)
                                   placeholder="{{ strtoupper($loc) }}" class="w-full rounded-md border-ink-300 text-sm">
                        @endforeach
                        <span class="shrink-0 font-mono text-xs text-ink-400">{{ $category->slug }}</span>
                        @if ($canEdit)
                            <button type="button" wire:click="saveTreatmentCategory({{ $category->id }})" class="shrink-0 text-xs font-medium text-brand-700 hover:underline">{{ __('Save') }}</button>
                            <button type="button" wire:click="deleteTreatmentCategory({{ $category->id }})" wire:confirm="{{ __('Delete this category?') }}" class="shrink-0 text-xs font-medium text-danger-500 hover:underline">{{ __('Delete') }}</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-3 text-sm text-ink-500">{{ __('No categories yet.') }}</p>
            @endforelse
        </div>

        @if ($canEdit)
            <div class="mt-4 border-t border-ink-100 pt-4">
                <p class="text-sm font-medium text-ink-700">{{ __('Add category') }}</p>
                <div class="mt-2 flex flex-wrap items-end gap-2">
                    <input type="text" wire:model="newTreatment.en" wire:blur="slugifyTreatment" placeholder="{{ __('Name (EN)') }}" class="rounded-md border-ink-300 text-sm">
                    <input type="text" wire:model="newTreatment.tr" placeholder="{{ __('Name (TR)') }}" class="rounded-md border-ink-300 text-sm">
                    <input type="text" wire:model="newTreatment.slug" placeholder="{{ __('slug') }}" class="w-28 rounded-md border-ink-300 font-mono text-sm">
                    <x-button type="button" wire:click="addTreatmentCategory" size="sm">{{ __('Add') }}</x-button>
                </div>
                @error('newTreatment.en') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                @error('newTreatment.slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    {{-- Post categories --}}
    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Post categories') }}</h2>

        <div class="mt-4 divide-y divide-ink-100">
            @forelse ($postCategories as $category)
                <div wire:key="pc-{{ $category->id }}" class="py-3">
                    <div class="flex items-center gap-2">
                        @foreach ($locales as $loc)
                            <input type="text" wire:model="postNames.{{ $category->id }}.{{ $loc }}" @disabled(! $canEdit)
                                   placeholder="{{ strtoupper($loc) }}" class="w-full rounded-md border-ink-300 text-sm">
                        @endforeach
                        <span class="shrink-0 font-mono text-xs text-ink-400">{{ $category->slug }}</span>
                        @if ($canEdit)
                            <button type="button" wire:click="savePostCategory({{ $category->id }})" class="shrink-0 text-xs font-medium text-brand-700 hover:underline">{{ __('Save') }}</button>
                            <button type="button" wire:click="deletePostCategory({{ $category->id }})" wire:confirm="{{ __('Delete this category?') }}" class="shrink-0 text-xs font-medium text-danger-500 hover:underline">{{ __('Delete') }}</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-3 text-sm text-ink-500">{{ __('No categories yet.') }}</p>
            @endforelse
        </div>

        @if ($canEdit)
            <div class="mt-4 border-t border-ink-100 pt-4">
                <p class="text-sm font-medium text-ink-700">{{ __('Add category') }}</p>
                <div class="mt-2 flex flex-wrap items-end gap-2">
                    <input type="text" wire:model="newPost.en" wire:blur="slugifyPost" placeholder="{{ __('Name (EN)') }}" class="rounded-md border-ink-300 text-sm">
                    <input type="text" wire:model="newPost.tr" placeholder="{{ __('Name (TR)') }}" class="rounded-md border-ink-300 text-sm">
                    <input type="text" wire:model="newPost.slug" placeholder="{{ __('slug') }}" class="w-28 rounded-md border-ink-300 font-mono text-sm">
                    <x-button type="button" wire:click="addPostCategory" size="sm">{{ __('Add') }}</x-button>
                </div>
                @error('newPost.en') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                @error('newPost.slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>
</div>
