<?php

namespace App\Providers;

use App\Models\Country;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Lets App\Services\ImageProcessor (and the upload Actions that
        // depend on it) resolve out of the container. GD is used rather
        // than Imagick — it's the extension available in this environment
        // and covers the JPEG/WebP work we need (see ImageProcessor).
        $this->app->bind(ImageManager::class, fn () => new ImageManager(new Driver));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.public', function ($view) {
            $view->with('footerCountries', Country::where('is_target', true)->orderBy('name')->get());
        });
    }
}
