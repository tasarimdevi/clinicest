<?php

declare(strict_types=1);

use App\Http\Controllers\DocumentDownloadController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/web/public.php';
require __DIR__.'/web/auth.php';
require __DIR__.'/web/patient.php';
require __DIR__.'/web/clinic.php';
require __DIR__.'/web/admin.php';
require __DIR__.'/web/settings.php';

// Shared between the clinic portal and admin — DocumentPolicy::view()
// does the actual authorization (clinic-membership scoping or
// access-admin), not route middleware, since callers come from two
// different route groups.
Route::get('/documents/{document}/download', DocumentDownloadController::class)
    ->middleware('auth')
    ->name('documents.download');
