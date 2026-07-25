<?php

declare(strict_types=1);

use App\Livewire\Admin\BeforeAfterModeration;
use App\Livewire\Admin\Billing;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\ClinicApplications;
use App\Livewire\Admin\ClinicForm;
use App\Livewire\Admin\Clinics;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DoctorForm;
use App\Livewire\Admin\Doctors;
use App\Livewire\Admin\FaqForm;
use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\LeadDetail;
use App\Livewire\Admin\Leads;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\Posts;
use App\Livewire\Admin\ReviewModeration;
use App\Livewire\Admin\TreatmentForm;
use App\Livewire\Admin\Treatments;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
| See docs/09-crm-admin-architecture.md §4 (admin panel) and
| docs/04-wireframes.md §16. CRM lead inbox, clinic directory + verification
| workflow, doctor directory, and content (Guide/Blog) are built out below —
| SEO tooling, media library, users, billing remain Phase 2+
| (docs/10-roadmap.md). Gated on the 'access-admin' permission, seeded by
| RolePermissionSeeder; each sub-area then narrows further via its own
| policy (LeadPolicy, ClinicPolicy, DoctorPolicy, PostPolicy).
*/

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', Leads::class)->name('index');
        Route::get('/{lead}', LeadDetail::class)->name('show');
    });

    Route::prefix('clinics')->name('clinics.')->group(function () {
        Route::get('/', Clinics::class)->name('index');
        Route::get('/applications', ClinicApplications::class)->name('applications');
        Route::get('/create', ClinicForm::class)->name('create');
        Route::get('/{clinic}/edit', ClinicForm::class)->name('edit');
    });

    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', Doctors::class)->name('index');
        Route::get('/create', DoctorForm::class)->name('create');
        Route::get('/{doctor}/edit', DoctorForm::class)->name('edit');
    });

    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', Posts::class)->name('index');
        Route::get('/create', PostForm::class)->name('create');
        Route::get('/{post}/edit', PostForm::class)->name('edit');
    });

    Route::prefix('treatments')->name('treatments.')->group(function () {
        Route::get('/', Treatments::class)->name('index');
        Route::get('/create', TreatmentForm::class)->name('create');
        Route::get('/{treatment}/edit', TreatmentForm::class)->name('edit');
    });

    Route::prefix('faqs')->name('faqs.')->group(function () {
        Route::get('/', Faqs::class)->name('index');
        Route::get('/create', FaqForm::class)->name('create');
        Route::get('/{faq}/edit', FaqForm::class)->name('edit');
    });

    Route::get('/categories', Categories::class)->name('categories.index');

    Route::get('/reviews', ReviewModeration::class)->name('reviews.index');
    Route::get('/before-after', BeforeAfterModeration::class)->name('before-after.index');
    Route::get('/billing', Billing::class)->name('billing.index');
});
