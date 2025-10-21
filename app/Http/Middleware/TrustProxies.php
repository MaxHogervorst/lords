<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * We trust only private network ranges (Digital Ocean load balancer).
     * Real client IP is extracted by SetCloudflareIp middleware
     * which reads the DO-Connecting-IP header from Digital Ocean's load balancer.
     *
     * This approach requires zero maintenance - no need to update IP ranges.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [
        // Private network ranges (Digital Ocean load balancer)
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',

        // Localhost/loopback
        '127.0.0.0/8',
        '::1',
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_PREFIX;
}

