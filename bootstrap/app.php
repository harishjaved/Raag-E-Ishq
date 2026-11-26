<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (\Throwable $e) {
            if (!app()->runningUnitTests()) {
                $frames = collect($e->getTrace())
                    ->take(5)
                    ->map(fn ($f) => basename((string) ($f['file'] ?? '?')) . ':' . ($f['line'] ?? '?'))
                    ->implode(' <- ');
                error_log('APP_EXCEPTION: ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine() . ' | ' . $frames);
                if (isset($_SERVER['VERCEL'])) {
                    return false;
                }
            }
        });
    })->create();
