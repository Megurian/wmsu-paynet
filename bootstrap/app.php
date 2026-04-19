<?php

use App\Exceptions\PromissoryNoteException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/student.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'student' => \App\Http\Middleware\AuthenticateStudent::class,
            'guest.student' => \App\Http\Middleware\GuestStudent::class,
        ]);

        $middleware->append([
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\SecureHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
        $exceptions->dontReport([
            PromissoryNoteException::class,
        ]);
    })->create();
