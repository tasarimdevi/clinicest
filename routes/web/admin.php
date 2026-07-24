<?php

declare(strict_types=1);

use App\Livewire\Admin\ClinicForm;
use App\Livewire\Admin\Clinics;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DoctorForm;
use App\Livewire\Admin\Doctors;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Admin\Leads;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §4 (admin panel) and
| docs/04-wireframes.md §16. CRM lead inbox, clinic directory + verification
| workflow, and doctor directory are built out below — content, SEO, media,
| users, billing remain Phase 2+ (docs/10-roadmap.md). Gated on the
| 'access-admin' permission, seeded by RolePermissionSeeder; each sub-area
| then narrows further via its own policy (LeadPolicy, ClinicPolicy, DoctorPolicy).
*/

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', Leads::class)->name('index');
        Route::get('/{lead}', LeadDetail::class)->name('show');
    });

    Route::prefix('clinics')->name('clinics.')->group(function () {
        Route::get('/', Clinics::class)->name('index');
        Route::get('/create', ClinicForm::class)->name('create');
        Route::get('/{clinic}/edit', ClinicForm::class)->name('edit');
    });

    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', Doctors::class)->name('index');
        Route::get('/create', DoctorForm::class)->name('create');
        Route::get('/{doctor}/edit', DoctorForm::class)->name('edit');
    });
});
