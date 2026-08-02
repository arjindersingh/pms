<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CompanyProfile;

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
        if ($vercelUrl = env('VERCEL_URL')) {
            URL::forceRootUrl('https://'.$vercelUrl);
            URL::forceScheme('https');
        }

        $this->ignoreStaleViteHotFile();
        View::composer('*', fn ($view) => $view->with('companyProfile', CompanyProfile::current()));

        // Root-relative build URLs work behind forwarded ports, HTTPS proxies,
        // and IDE previews without relying on APP_URL host detection.
        Vite::createAssetPathsUsing(
            fn (string $path, ?bool $secure = null): string => '/'.ltrim($path, '/'),
        );
    }

    /**
     * Fall back to compiled assets when an interrupted Vite process leaves its
     * hot file behind. Non-local environments must never depend on a dev server.
     */
    private function ignoreStaleViteHotFile(): void
    {
        $hotFile = public_path('hot');

        if (! file_exists($hotFile)) {
            return;
        }

        if (! $this->app->environment('local') || ! $this->viteServerIsReachable($hotFile)) {
            Vite::useHotFile(storage_path('framework/vite.hot'));
        }
    }

    private function viteServerIsReachable(string $hotFile): bool
    {
        $url = trim((string) file_get_contents($hotFile));
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (! is_string($host) || ! is_int($port)) {
            return false;
        }

        $connection = @fsockopen($host, $port, $errorCode, $errorMessage, 0.1);

        if ($connection === false) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
