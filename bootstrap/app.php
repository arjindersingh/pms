<?php

use App\Http\Middleware\EnsureMenuPermission;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureUserCategory;
use App\Http\Middleware\CheckSystemCompatibility;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cloud and IDE previews terminate HTTPS at a reverse proxy. Trust its
        // forwarded host/protocol so generated auth links stay on the public URL.
        $middleware->trustProxies(at: '*');

        $middleware->appendToGroup('web', CheckSystemCompatibility::class);
        $middleware->appendToGroup('web', TrackUserSession::class);
        $middleware->validateCsrfTokens(except: ['payments/webhook/*']);

        $middleware->alias([
            'category' => EnsureUserCategory::class,
            'module' => EnsureModuleAccess::class,
            'menu' => EnsureMenuPermission::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin/*') ? route('administrator.login') : route('login'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
