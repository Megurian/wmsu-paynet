<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // global middleware
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\CheckActiveSchoolYear::class,
            // other middleware like auth, etc.
        ],
    ];

    protected $routeMiddleware = [
        'check.active.sy' => \App\Http\Middleware\CheckActiveSchoolYear::class,
    ];
}
