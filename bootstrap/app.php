<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.auth' => \App\Http\Middleware\TenantAuth::class,
            'api.tenant.context' => \App\Http\Middleware\Api\SetTenantContextMiddleware::class,
            'api.admin.only' => \App\Http\Middleware\Api\AdminOnlyMiddleware::class,
            'api.auth' => \App\Http\Middleware\Api\AuthMiddleware::class,
            'web.active.tenant' => \App\Http\Middleware\ActiveTenantMiddleware::class,
            'web.active.tenant.except.admin' => \App\Http\Middleware\ActiveTenantExceptAdminMiddleware::class,
            'api.active.tenant' => \App\Http\Middleware\Api\ActiveTenantMiddleware::class,
            'admin.only' => \App\Http\Middleware\AdminOnlyMiddleware::class,
            'main.admin.only' => \App\Http\Middleware\MainAdminOnlyMiddleware::class,
            'web.tenant.subscribed' => \App\Http\Middleware\TenantSubscribedMiddleware::class,
            'api.tenant.subscribed' => \App\Http\Middleware\Api\TenantSubscribedMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // AccessDeniedHttpException handler
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $exception) {
            return response()->json([
                'message' => 'This action is unauthorized.',
                'error' => 'unauthorized',
            ], 403);
        });

        // sentry integration
        Integration::handles($exceptions);
    })->create();
