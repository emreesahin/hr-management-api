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
    ->withMiddleware(function (Middleware $middleware) {
        // Spatie Permission Middleware'ları
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // API için global middleware'lar
        $middleware->api([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1', // Rate limiting buraya taşındı
        ]);

        // HR uygulamasına özel middleware grupları
        $middleware->group('hr', [
            'auth:sanctum',
            'permission:access hr dashboard',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Özel exception handler'lar
        $exceptions->render(function (Spatie\Permission\Exceptions\UnauthorizedException $e) {
            return response()->json([
                'message' => 'Bu işlem için yetkiniz bulunmamaktadır.',
                'error' => $e->getMessage()
            ], 403);
        });

        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'message' => 'Oturum açmanız gerekiyor',
                'error' => $e->getMessage()
            ], 401);
        });
    })->create();
