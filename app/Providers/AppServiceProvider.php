<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Root-relative build URLs work behind forwarded ports, HTTPS proxies,
        // and IDE previews without relying on APP_URL host detection.
        Vite::createAssetPathsUsing(
            fn (string $path, ?bool $secure = null): string => '/'.ltrim($path, '/'),
        );
    }
}
