<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->environment('production')) {
            $policy = "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
                "style-src 'self' 'unsafe-inline' https:; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' https: data:; " .
                "connect-src 'self' https:; " .
                "frame-ancestors 'self'; " . // clickjacking protection
                "base-uri 'self'; " .
                "form-action 'self';";

            $response->headers->set('Content-Security-Policy', $policy);
        }

        return $response;
    }
}
