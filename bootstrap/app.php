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
    ->withCommands([
        App\Modules\Identity\Infrastructure\Console\CreateUserCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Activa el middleware stateful de Sanctum al grup `api` perquè les
        // peticions del panell SPA (admin.inclinio.localhost) s'autentiquin
        // via la cookie de sessió en lloc d'un Bearer token.
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \App\Modules\Identity\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
