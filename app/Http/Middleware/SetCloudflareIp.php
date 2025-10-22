<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloudflare-specific middleware to extract the real client IP from CF-Connecting-IP header.
 *
 * This is more reliable than maintaining a list of Cloudflare IP ranges.
 * Cloudflare always sends the CF-Connecting-IP header with the real client IP.
 *
 * This middleware should run BEFORE TrustProxies.
 */
class SetCloudflareIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If CF-Connecting-IP header exists (request came through Cloudflare)
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            // Prepend the real IP to X-Forwarded-For
            $existingForwardedFor = $request->header('X-Forwarded-For', '');
            $newForwardedFor = $existingForwardedFor
                ? "{$cfIp},{$existingForwardedFor}"
                : $cfIp;

            $request->headers->set('X-Forwarded-For', $newForwardedFor);
        }

        return $next($request);
    }
}
