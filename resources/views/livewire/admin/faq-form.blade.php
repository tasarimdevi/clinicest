<div class="max-w-2xl">
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <div class="space-y-4">
                <x-translatable-field field="question" type="textarea" :rows="2" :label="__('Question')" />
                <x-translatable-field field="answer" type="textarea" :rows="5" :label="__('Answer')" />
            </div>
        </div>

        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Attach to treatment (optional)') }}</label>
                    <select wire:model="treatment_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Global (FAQ hub)') }}</option>
                        @foreach ($treatments as $t)
                            <option value="{{ $t->id }}">{{ $t->getTranslation('name', 'en') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Category (optional)') }}</label>
                    <input type="text" wire:model="category" placeholder="e.g. Pricing" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Sort order') }}</label>
                    <input type="number" min="0" wire:model="sort" class="mt-1.5 w-24 rounded-md border-ink-300 text-sm">
                    @error('sort') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Status') }}</label>
                    <select wire:model="status" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="draft">{{ __('Draft') }}</option>
                        <option value="published">{{ __('Published') }}</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
            {{ $faq ? __('Save changes') : __('Create FAQ') }}
        </x-button>
    </form>
</div>
