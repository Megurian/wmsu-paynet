<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Use 127.0.0.1 instead of [::1]
        if (app()->environment('local')) {
            $viteAddress = "http://localhost:5173 http://127.0.0.1:5173";
            $viteWs = "ws://localhost:5173 ws://127.0.0.1:5173";
            
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-eval' $viteAddress",
                "style-src 'self' 'unsafe-inline' $viteAddress",
                "connect-src 'self' $viteWs $viteAddress",
            ];
        } else {
            // Strict production policy for when wmsu-paynet goes live
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self'",
                "connect-src 'self'",
            ];
        }

        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', $cspDirectives));
        
        // ... rest of your security headers ...
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), gyroscope=(), accelerometer=(), fullscreen=(), sync-xhr=()');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
