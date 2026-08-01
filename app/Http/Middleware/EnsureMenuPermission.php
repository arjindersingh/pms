<?php

namespace App\Http\Middleware;

use App\Services\PortalAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuPermission
{
    public function __construct(private readonly PortalAccess $access) {}

    public function handle(Request $request, Closure $next, string $menu, string $ability = 'view'): Response
    {
        abort_unless($request->user() && $this->access->menu($request->user(), $menu, $ability), 403);

        return $next($request);
    }
}
