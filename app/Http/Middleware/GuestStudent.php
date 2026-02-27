<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        return $next($request);
    }
}
