<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Handle port and protocol shifts behind Caddy/Proxies
        $middleware->trustProxies(at: '*');

        // Global API Rate Limit (OWASP A04)
        $middleware->throttleApi('60,1');

        // API Aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // API guest handling (do not redirect API requests to login page)
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 1. Validation errors (e.g., past dates) sent back to frontend
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 2. Resource not found (404)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                    'error_code' => 'RESOURCE_NOT_FOUND'
                ], 404);
            }
        });

        // 3. Handling all other unexpected errors (500) securely
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error("Server Error: " . $e->getMessage(), [
                    'url' => $request->fullUrl(),
                    'input' => $request->except(['password', 'google2fa_secret'])
                ]);
                return response()->json([
                    'message' => 'Internal server error occurred.',
                    'error_code' => 'SERVER_ERROR'
                ], 500);
            }
        });
    })->create();
