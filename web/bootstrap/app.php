<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add session and cookie middlewares to API group for SPA authentication
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AuditLogger::class,
        ]);

        // Add security headers to all responses
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        ]);

        // CSRF Protection: Only exclude truly stateless endpoints
        // Sanctum handles CSRF for stateful requests via EnsureFrontendRequestsAreStateful
        $middleware->validateCsrfTokens(except: [
            'api/v1/frontend/guest-chat/*',  // Stateless guest chat only
            'sanctum/csrf-cookie',            // CSRF cookie endpoint itself
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report all exceptions to appropriate error log channel
        $exceptions->report(function (\Throwable $e) {
            $channel = \App\Logging\ErrorChannelResolver::getChannel($e);
            \Log::channel($channel)->error($e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        // Handle authentication exceptions for API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        // Handle custom exceptions for API
        $exceptions->render(function (\App\Exceptions\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render($request);
            }
        });

        $exceptions->render(function (\App\Exceptions\NotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render($request);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $exceptions->render(function (\App\Exceptions\BusinessLogicException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render($request);
            }
        });

        $exceptions->render(function (\App\Exceptions\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render($request);
            }
        });

        // Handle all other exceptions for API
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Log the exception to appropriate channel
                $channel = \App\Logging\ErrorChannelResolver::getChannel($e);
                \Log::channel($channel)->error('Unhandled exception', [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Handle 404 errors (including ModelNotFoundException which usually gets converted)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || 
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Resource not found',
                    ], 404);
                }

                // Return error response for all other exceptions
                // SECURITY: Never expose debug info in production
                return response()->json([
                    'status' => 'error',
                    'message' => (config('app.debug') && config('app.env') !== 'production')
                        ? $e->getMessage()
                        : 'Internal server error',
                    'exception' => (config('app.debug') && config('app.env') !== 'production')
                        ? get_class($e)
                        : null,
                    'file' => (config('app.debug') && config('app.env') !== 'production')
                        ? $e->getFile()
                        : null,
                    'line' => (config('app.debug') && config('app.env') !== 'production')
                        ? $e->getLine()
                        : null,
                    'trace_id' => \Illuminate\Support\Str::uuid()->toString(), // For support debugging
                ], 500);
            }
        });
    })->create();
