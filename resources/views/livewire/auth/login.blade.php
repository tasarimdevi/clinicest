<div>
    <h1 class="text-lg font-semibold text-ink-900">{{ __('Sign in') }}</h1>
    <p class="mt-1 text-sm text-ink-500">{{ __('Admin, clinic, and patient accounts sign in here.') }}</p>

    <form wire:submit="submit" class="mt-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-ink-700">{{ __('Email') }}</label>
            <input type="email" wire:model="email" autofocus class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
            @error('email') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink-700">{{ __('Password') }}</label>
            <input type="password" wire:model="password" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
            @error('password') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-600">
            <input type="checkbox" wire:model="remember" class="rounded border-ink-300">
            {{ __('Remember me') }}
        </label>

        <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="submit">
            {{ __('Sign in') }}
        </x-button>
    </form>
</div>
