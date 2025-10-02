<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('/');
        }

        return $next($request);
    }
}
