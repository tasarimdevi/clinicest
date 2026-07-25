<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Documents') }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ __('Certificates and verification uploads for :clinic, plus treatment plans or x-rays for specific patients.', ['clinic' => $clinic->getTranslation('name', app()->getLocale())]) }}
        </p>
    </div>

    @can('create', \App\Models\Document::class)
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Upload a document') }}</h2>
            <form wire:submit="upload" class="mt-4 space-y-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Type') }}</label>
                        <select wire:model="type" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Related patient (optional)') }}</label>
                        <select wire:model="lead_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('None — clinic-level document') }}</option>
                            @foreach ($acceptedLeads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Title') }}</label>
                    <input type="text" wire:model="title" placeholder="{{ __('e.g. ISO 9001 Certificate') }}" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('title') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('File') }}</label>
                    <input type="file" wire:model="file" class="mt-1.5 w-full text-sm">
                    <p class="mt-1 text-xs text-ink-500">{{ __('PDF, JPG, PNG, or Word — up to 10MB.') }}</p>
                    @error('file') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="file" class="mt-1 text-xs text-ink-500">{{ __('Uploading…') }}</div>
                </div>
                <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="upload">
                    <span wire:loading.remove wire:target="upload">{{ __('Upload') }}</span>
                    <span wire:loading wire:target="upload">{{ __('Saving…') }}</span>
                </x-button>
            </form>
        </div>
    @endcan

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Uploaded documents') }}</h2>
        <ul class="mt-4 divide-y divide-ink-100">
            @forelse ($documents as $document)
                <li class="flex items-center justify-between py-3 text-sm">
                    <div>
                        <p class="font-medium text-ink-900">{{ $document->title }}</p>
                        <p class="text-xs text-ink-500">
                            {{ $document->type->label() }}
                            @if ($document->lead) &middot; {{ $document->lead->full_name }} @endif
                            &middot; {{ $document->created_at->format('d M Y') }}
                            @if ($document->uploadedBy) &middot; {{ $document->uploadedBy->name }} @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('documents.download', $document) }}" class="font-medium text-brand-600 hover:underline">{{ __('Download') }}</a>
                        @can('delete', $document)
                            <button type="button" wire:click="delete({{ $document->id }})"
                                    wire:confirm="{{ __('Delete this document?') }}"
                                    class="font-medium text-danger-500 hover:underline">
                                {{ __('Delete') }}
                            </button>
                        @endcan
                    </div>
                </li>
            @empty
                <li class="py-3 text-sm text-ink-500">{{ __('No documents uploaded yet.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
