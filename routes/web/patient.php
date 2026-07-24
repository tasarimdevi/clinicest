<?php

declare(strict_types=1);

use App\Livewire\Patient\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patient portal routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §2 (patient portal) and
| docs/04-wireframes.md §16. Only a minimal landing page is built out —
| offers, messages, appointments, and reviews are Phase 3
| (docs/10-roadmap.md). Livewire components will live in app/Livewire/Patient/*.
*/

Route::middleware(['auth'])->prefix('account')->name('patient.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
});
