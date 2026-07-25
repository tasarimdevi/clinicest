<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Treatment') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-translatable-field field="name" :label="__('Name')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Slug') }}</label>
                        <input type="text" wire:model="slug" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm">
                        @error('slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <x-translatable-field field="summary" type="textarea" :rows="2" :label="__('Summary')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-translatable-field field="body" type="textarea" :rows="8" :label="__('Body')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Category') }}</label>
                        <select wire:model="category_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->getTranslation('name', 'en') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Icon (optional)') }}</label>
                        <input type="text" wire:model="icon" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Pricing & logistics') }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Currency') }}</label>
                        <input type="text" wire:model="currency" maxlength="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm uppercase">
                        @error('currency') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Price from') }}</label>
                        <input type="number" step="0.01" min="0" wire:model="priceMin" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('priceMin') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Price to') }}</label>
                        <input type="number" step="0.01" min="0" wire:model="priceMax" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('priceMax') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Avg duration (min)') }}</label>
                        <input type="number" min="0" wire:model="avg_duration_min" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Recovery (days)') }}</label>
                        <input type="number" min="0" wire:model="recovery_days" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Trips required') }}</label>
                        <input type="number" min="0" wire:model="trips_required" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                </div>
            </div>

            <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
                {{ $treatment ? __('Save changes') : __('Create treatment') }}
            </x-button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Settings') }}</h2>
            <div class="mt-3 flex flex-col gap-3">
                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" wire:model="is_featured" class="rounded border-ink-300">
                    {{ __('Featured on homepage') }}
                </label>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Sort order') }}</label>
                    <input type="number" min="0" wire:model="sort" class="mt-1.5 w-24 rounded-md border-ink-300 text-sm">
                    @error('sort') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @if ($treatment)
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Status') }}</h2>
                <p class="mt-1 text-xs text-ink-500">{{ ucfirst($status) }}</p>
                @can('publish', $treatment)
                    <div class="mt-3">
                        @if ($status === 'published')
                            <button type="button" wire:click="unpublish" class="text-sm font-medium text-ink-500 hover:underline">{{ __('Unpublish') }}</button>
                        @else
                            <button type="button" wire:click="publish" class="text-sm font-medium text-success-600 hover:underline">{{ __('Publish') }}</button>
                        @endif
                    </div>
                @endcan
            </div>
        @else
            <div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-6 text-sm text-ink-500">
                {{ __('Save the treatment first — new treatments start as drafts.') }}
            </div>
        @endif
    </div>
</div>
