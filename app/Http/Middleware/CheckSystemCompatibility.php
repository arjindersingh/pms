<?php

namespace App\Http\Middleware;

use App\Services\SystemCompatibility;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemCompatibility
{
    public function __construct(private readonly SystemCompatibility $compatibility) {}

    public function handle(Request $request, Closure $next): Response
    {
        View::share('systemCompatibility', $this->compatibility->assess($request->userAgent()));

        return $next($request);
    }
}
