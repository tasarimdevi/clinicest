<?php

declare(strict_types=1);

use App\Livewire\Clinic\AppointmentScheduler;
use App\Livewire\Clinic\ClinicBeforeAfter;
use App\Livewire\Clinic\ClinicBilling;
use App\Livewire\Clinic\ClinicDocuments;
use App\Livewire\Clinic\ClinicProfile;
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
| assignments) + offer builder + appointment scheduler + messages +
| documents + profile editor are built out below — commissions/
| subscription billing (needs a payment provider) is the one remaining
| Phase 3 item (docs/10-roadmap.md). Scoped to clinic members only via
| the `clinic.member` middleware (app/Http/Middleware/EnsureClinicMember.php).
*/

Route::middleware(['auth', 'clinic.member'])->prefix('clinic/{clinic}')->name('clinic.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/profile', ClinicProfile::class)->name('profile');
    Route::get('/before-after', ClinicBeforeAfter::class)->name('before-after');
    Route::get('/leads', LeadInbox::class)->name('leads');
    Route::get('/leads/{lead}/offer', OfferBuilder::class)->name('offers.create');
    Route::get('/leads/{lead}/appointments', AppointmentScheduler::class)->name('appointments.index');
    Route::get('/leads/{lead}/messages', MessageThread::class)->name('messages.index');
    Route::get('/documents', ClinicDocuments::class)->name('documents.index');
    Route::get('/billing', ClinicBilling::class)->name('billing');
});
