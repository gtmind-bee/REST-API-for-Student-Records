<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // This API uses Sanctum bearer tokens; no browser-session middleware is needed.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel's default JSON exception handling is used for API requests.
    })
    ->create();
