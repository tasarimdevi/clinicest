<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §4 (admin panel) and
| docs/04-wireframes.md §16. Built out from Phase 1 (CRM inbox) through
| Phase 4 (docs/10-roadmap.md). Livewire components will live in
| app/Livewire/Admin/*, gated by Spatie permissions (docs/09 §1).
*/

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    // Route::get('/', Dashboard::class)->name('dashboard');
    // Route::get('/clinics', Clinics::class)->name('clinics');
    // Route::get('/doctors', Doctors::class)->name('doctors');
    // Route::get('/leads', Leads::class)->name('leads'); // CRM inbox — Phase 1
    // Route::get('/treatments', Treatments::class)->name('treatments');
    // Route::get('/countries', Countries::class)->name('countries');
    // Route::get('/posts', Posts::class)->name('posts');
    // Route::get('/reviews', Reviews::class)->name('reviews');
    // Route::get('/seo', Seo::class)->name('seo');
    // Route::get('/media', Media::class)->name('media');
    // Route::get('/users', Users::class)->name('users');
    // Route::get('/payments', Payments::class)->name('payments');
    // Route::get('/commissions', Commissions::class)->name('commissions');
    // Route::get('/invoices', Invoices::class)->name('invoices');
    // Route::get('/settings', Settings::class)->name('settings');
});
