<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extract the real client IP from Digital Ocean's DO-Connecting-IP header.
 *
 * Digital Ocean load balancers send the real client IP in the DO-Connecting-IP header.
 * This is more reliable than maintaining lists of Cloudflare IP ranges.
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
        // Check for DO-Connecting-IP header (Digital Ocean load balancer)
        if ($realIp = $request->header('DO-Connecting-IP')) {
            // Replace X-Forwarded-For with the real client IP
            // This ensures TrustProxies extracts the correct IP
            $request->headers->set('X-Forwarded-For', $realIp);

            // Keep REMOTE_ADDR as the load balancer IP (trusted proxy)
            // so TrustProxies middleware will process the headers
            // If REMOTE_ADDR is not already set to a private IP, set it to a trusted one
            $remoteAddr = $request->server->get('REMOTE_ADDR');
            if (!$this->isPrivateIp($remoteAddr)) {
                // Set to a trusted private IP so TrustProxies will trust it
                $request->server->set('REMOTE_ADDR', '10.0.0.1');
            }
        }

        return $next($request);
    }

    /**
     * Check if an IP is in private ranges.
     */
    private function isPrivateIp(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
