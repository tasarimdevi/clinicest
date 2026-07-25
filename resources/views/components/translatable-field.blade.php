@props([
    'field',
    'label',
    'type' => 'text',
    'rows' => 3,
    'placeholder' => '',
    'locales' => null,
])

@php
    // Defaults to the app's supported locales (config/clinicest.php). The
    // first locale is the canonical one (required); the rest are optional
    // translations that fall back to it when left blank.
    $locales ??= collect(config('clinicest.locales.supported', ['en']))
        ->mapWithKeys(fn ($code) => [$code => strtoupper($code)])
        ->all();
    $primary = array_key_first($locales);
@endphp

<div x-data="{ loc: '{{ $primary }}' }">
    <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-ink-700">{{ $label }}</label>
        <div class="flex gap-1">
            @foreach ($locales as $code => $name)
                <button type="button" @click="loc = '{{ $code }}'"
                        :class="loc === '{{ $code }}' ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-500 hover:bg-ink-200'"
                        class="rounded px-2 py-0.5 font-mono text-[11px] font-semibold">{{ $name }}</button>
            @endforeach
        </div>
    </div>

    @foreach ($locales as $code => $name)
        <div x-show="loc === '{{ $code }}'" x-cloak @if (! $loop->first) style="display: none;" @endif class="mt-1.5">
            @if ($type === 'textarea')
                <textarea wire:model="{{ $field }}.{{ $code }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                          class="w-full rounded-md border-ink-300 text-sm"></textarea>
            @else
                <input type="text" wire:model="{{ $field }}.{{ $code }}" placeholder="{{ $placeholder }}"
                       class="w-full rounded-md border-ink-300 text-sm">
            @endif
            @error($field.'.'.$code) <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
        </div>
    @endforeach
</div>
