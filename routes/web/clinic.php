<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clinic portal (partner SaaS) routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §3 (clinic dashboard) and
| docs/04-wireframes.md §16. Built out in Phase 3 (docs/10-roadmap.md).
| Livewire components will live in app/Livewire/Clinic/*, scoped by the
| `clinic.member` middleware alias (app/Http/Middleware/EnsureClinicMember.php).
*/

Route::middleware(['auth'])->prefix('clinic/{clinic}')->name('clinic.')->group(function () {
    // Route::middleware('clinic.member')->group(function () {
    //     Route::get('/', Dashboard::class)->name('dashboard');
    //     Route::get('/leads', LeadInbox::class)->name('leads');
    //     Route::get('/messages', Messages::class)->name('messages');
    //     Route::get('/appointments', Appointments::class)->name('appointments');
    //     Route::get('/documents', Documents::class)->name('documents');
    //     Route::get('/treatment-plans', TreatmentPlans::class)->name('treatment-plans');
    //     Route::get('/commissions', CommissionReports::class)->name('commissions');
    //     Route::get('/subscription', Subscription::class)->name('subscription');
    //     Route::get('/profile', ProfileEditor::class)->name('profile');
    // });
});
