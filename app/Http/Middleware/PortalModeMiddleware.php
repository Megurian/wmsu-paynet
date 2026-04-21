<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PortalModeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $mode = session('portal_mode', 'student');

        // optional global sharing
        view()->share('portal_mode', $mode);

        return $next($request);
    }
}