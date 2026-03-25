<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers for all web responses
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Registrar aliases de middleware que o aplicativo espera usar nas rotas
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
