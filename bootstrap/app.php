<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\PreventRapidPosts;
use App\Http\Middleware\VerifyApiToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['tap-absen', 'api/uploadpodcast']);

        $middleware->alias([
            'api.token' => VerifyApiToken::class,
            'role' => EnsureUserHasRole::class,
            'actioncheck' => PreventRapidPosts::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
