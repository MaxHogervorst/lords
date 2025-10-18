<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsScheme
{
    /**
     * Handle an incoming request and force HTTPS scheme detection.
     *
     * This middleware forces Laravel to recognize the connection as HTTPS
     * when X-Forwarded-Proto header is present, regardless of whether
     * TrustProxies is working correctly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If we're behind a proxy that sends X-Forwarded-Proto: https
        // Force the request to be treated as secure
        if ($request->header('X-Forwarded-Proto') === 'https') {
            $request->server->set('HTTPS', 'on');
        }

        return $next($request);
    }
}
