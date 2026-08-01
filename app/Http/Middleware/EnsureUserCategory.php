<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCategory
{
    public function handle(Request $request, Closure $next, string ...$categories): Response
    {
        $category = $request->user()?->userType?->category->value;

        abort_unless($category && in_array($category, $categories, true), 403);

        return $next($request);
    }
}
