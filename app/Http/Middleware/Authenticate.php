<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (! Auth::guard($guard)->check()) {
            if ($request->expectsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('auth/login');
        }

        return $next($request);
    }
}
