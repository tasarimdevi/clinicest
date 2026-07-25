<?php

declare(strict_types=1);

use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\PatientPortal;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patient portal routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §2 (patient portal) and
| docs/04-wireframes.md §16. The "Registered Patient" (auth) role only has
| a minimal landing page so far. The "Patient (lead)" magic-link role below
| is fully built: a Laravel-signed URL (see PatientPortalLinkMail) is the
| entire access control — no auth guard, keyed on Lead::public_id so the
| link can't be used to enumerate leads.
*/

Route::middleware(['auth'])->prefix('account')->name('patient.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
});

Route::middleware(['signed'])->prefix('portal')->name('patient.portal.')->group(function () {
    Route::get('/{lead:public_id}', PatientPortal::class)->name('show');
});
