<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to add to every response.
     */
    private array $headers = [
        'X-Content-Type-Options'  => 'nosniff',
        'X-Frame-Options'         => 'DENY',
        'X-XSS-Protection'        => '1; mode=block',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' https://cdn.tailwindcss.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https://picsum.photos https://fastly.picsum.photos https://images.unsplash.com; font-src 'self' https://fonts.gstatic.com; form-action 'self'; frame-ancestors 'none';",
    ];

    /**
     * Strip these headers if set (e.g. by PHP/nginx in dev).
     */
    private array $stripHeaders = [
        'X-Powered-By',
        'Server',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        foreach ($this->stripHeaders as $key) {
            // Remove Server header - symfony always sets it, so override with empty
            if ($response->headers->has($key)) {
                $response->headers->remove($key);
            }
        }

        // Symfony adds a Server header automatically; explicitly clear it
        $response->headers->remove('Server');

        return $response;
    }
}
