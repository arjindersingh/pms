<?php

namespace App\Http\Middleware;

use App\Services\UserSessionTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    public function __construct(private readonly UserSessionTracker $tracker) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $this->tracker->record($request, $response);

        return $response;
    }
}
