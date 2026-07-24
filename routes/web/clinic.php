<?php

declare(strict_types=1);

use App\Livewire\Clinic\Dashboard;
use App\Livewire\Clinic\LeadInbox;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clinic portal (partner SaaS) routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §3 (clinic dashboard) and
| docs/04-wireframes.md §16. Dashboard + lead inbox (accept/decline
| assignments) are built out below — messages, appointments, documents,
| treatment plans, commissions, subscription, profile editor remain
| Phase 3 (docs/10-roadmap.md). Scoped to clinic members only via the
| `clinic.member` middleware (app/Http/Middleware/EnsureClinicMember.php).
*/

Route::middleware(['auth', 'clinic.member'])->prefix('clinic/{clinic}')->name('clinic.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/leads', LeadInbox::class)->name('leads');
});
