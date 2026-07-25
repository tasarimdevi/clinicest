<?php

declare(strict_types=1);

use App\Livewire\Clinic\AppointmentScheduler;
use App\Livewire\Clinic\Dashboard;
use App\Livewire\Clinic\LeadInbox;
use App\Livewire\Clinic\MessageThread;
use App\Livewire\Clinic\OfferBuilder;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clinic portal (partner SaaS) routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §3 (clinic dashboard) and
| docs/04-wireframes.md §16. Dashboard + lead inbox (accept/decline
| assignments) + offer builder + appointment scheduler + messages are
| built out below — documents, commissions/subscription billing, profile
| editor remain Phase 3 (docs/10-roadmap.md). Scoped to clinic members
| only via the `clinic.member` middleware
| (app/Http/Middleware/EnsureClinicMember.php).
*/

Route::middleware(['auth', 'clinic.member'])->prefix('clinic/{clinic}')->name('clinic.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/leads', LeadInbox::class)->name('leads');
    Route::get('/leads/{lead}/offer', OfferBuilder::class)->name('offers.create');
    Route::get('/leads/{lead}/appointments', AppointmentScheduler::class)->name('appointments.index');
    Route::get('/leads/{lead}/messages', MessageThread::class)->name('messages.index');
});
