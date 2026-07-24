<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Minimal session-based login. Registration / password reset / email
 * verification are not wired yet — see docs/10-roadmap.md Phase 1 remainder.
 * Post-login redirect is role-based: admin/super_admin -> admin dashboard,
 * clinic members -> their clinic dashboard, everyone else -> patient account.
 */
#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $throttleKey = 'login:'.$this->email;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]));

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', __('These credentials do not match our records.'));

            return;
        }

        RateLimiter::clear($throttleKey);

        // Auth::attempt() already migrates the session ID on login
        // (SessionGuard::updateSession()) — no manual regenerate needed.
        $this->redirect($this->postLoginUrl(), navigate: false);
    }

    protected function postLoginUrl(): string
    {
        $user = Auth::user();

        if ($user->can('access-admin')) {
            return route('admin.dashboard');
        }

        if ($user->clinics()->exists()) {
            return route('clinic.dashboard', $user->clinics()->first());
        }

        return route('patient.dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
