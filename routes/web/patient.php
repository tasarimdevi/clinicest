<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patient portal routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §2 (patient portal) and
| docs/04-wireframes.md §16. Built out in Phase 3 (docs/10-roadmap.md).
| Livewire components will live in app/Livewire/Patient/*.
*/

Route::middleware(['auth'])->prefix('account')->name('patient.')->group(function () {
    // Route::get('/', Dashboard::class)->name('dashboard');
    // Route::get('/requests', RequestStatus::class)->name('requests');
    // Route::get('/offers', Offers::class)->name('offers');
    // Route::get('/messages', Messages::class)->name('messages');
    // Route::get('/appointments', Appointments::class)->name('appointments');
});
