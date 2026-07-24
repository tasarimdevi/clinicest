<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
| Session-based login/logout only. Registration, password reset, email
| verification, and 2FA (required for clinic/admin per
| docs/08-laravel-architecture.md §5) are not wired yet — see
| docs/10-roadmap.md Phase 1 remainder.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');
