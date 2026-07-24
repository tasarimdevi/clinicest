<?php

declare(strict_types=1);

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Admin\Leads;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §4 (admin panel) and
| docs/04-wireframes.md §16. The CRM lead inbox (Dashboard, Leads,
| LeadDetail) is built out below — everything else (clinics, doctors,
| content, SEO, media, users, billing) remains Phase 2+ (docs/10-roadmap.md).
| Gated on the 'access-admin' permission, seeded by RolePermissionSeeder.
*/

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', Leads::class)->name('index');
        Route::get('/{lead}', LeadDetail::class)->name('show');
    });
});
