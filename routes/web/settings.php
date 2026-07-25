<?php

declare(strict_types=1);

use App\Livewire\Settings\NotificationPreferences;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account settings routes
|--------------------------------------------------------------------------
| Any authenticated User (admin or clinic staff) — not scoped to an
| access-admin permission or a specific clinic, since notification
| preferences are personal, not role-gated. See docs/09-crm-admin-
| architecture.md §5.
*/

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/notifications', NotificationPreferences::class)->name('notifications');
});
