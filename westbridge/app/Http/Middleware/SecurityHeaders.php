<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers on every response.
 *
 * The Content-Security-Policy is sent in production only: during local
 * development Vite serves assets from its own port, which a strict policy
 * would block. 'unsafe-eval' is required by Alpine.js, which Livewire bundles.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->remove('X-Powered-By');

        if ($request->is('admin', 'admin/*')) {
            // Admin screens are never cached by the browser or a proxy, so
            // the Back button always asks the server again - and the server
            // sends a signed-out person to the sign-in page.
            $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $headers->set('Pragma', 'no-cache');
            $headers->set('Expires', '0');
        }

        if (app()->environment('production')) {
            if ($request->isSecure()) {
                $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }

            if (! $headers->has('Content-Security-Policy')) {
                $headers->set('Content-Security-Policy', implode('; ', [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com",
                    "style-src 'self' 'unsafe-inline'",
                    "img-src 'self' data: blob: https:",
                    "font-src 'self' data:",
                    "connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com",
                    'frame-src https://www.google.com https://maps.google.com',
                    "frame-ancestors 'self'",
                    "form-action 'self'",
                    "base-uri 'self'",
                    "object-src 'none'",
                ]));
            }
        }

        return $response;
    }
}
