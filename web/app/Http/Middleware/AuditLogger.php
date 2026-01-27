<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Audit Logger Middleware
 *
 * Logs important API actions for security audit and compliance.
 */
class AuditLogger
{
    /**
     * Sensitive fields that should be redacted from logs.
     *
     * @var array<string>
     */
    private array $sensitiveFields = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
        'credit_card',
        'cvv',
        'ssn',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log certain routes (admin actions, sensitive operations)
        if ($this->shouldLog($request)) {
            $this->log($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged.
     *
     * @param Request $request
     * @return bool
     */
    private function shouldLog(Request $request): bool
    {
        // Log admin routes
        if (str_starts_with($request->path(), 'api/v1/admin')) {
            return true;
        }

        // Log sensitive operations
        $sensitivePaths = [
            'api/v1/frontend/orders',
            'api/v1/chat',
            'api/v1/frontend/profile',
            'api/v1/broadcasting/auth',
        ];

        foreach ($sensitivePaths as $path) {
            if (str_starts_with($request->path(), $path)) {
                return true;
            }
        }

        // Only log state-changing methods for other routes
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Log the request and response.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    private function log(Request $request, Response $response): void
    {
        try {
            $requestData = $this->sanitizeData($request->all());

            AuditLog::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => $this->determineAction($request),
                'route' => $request->path(),
                'method' => $request->method(),
                'request_data' => $requestData,
                'response_status' => $response->getStatusCode(),
                'description' => $this->generateDescription($request),
            ]);
        } catch (\Throwable $e) {
            // Don't let logging failures break requests
            Log::error('Audit logging failed: ' . $e->getMessage(), [
                'exception' => $e,
                'route' => $request->path(),
            ]);
        }
    }

    /**
     * Sanitize sensitive data from request.
     *
     * @param array $data
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        return $data;
    }

    /**
     * Determine the action type based on request method.
     *
     * @param Request $request
     * @return string
     */
    private function determineAction(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'access',
        };
    }

    /**
     * Generate a description for the log entry.
     *
     * @param Request $request
     * @return string
     */
    private function generateDescription(Request $request): string
    {
        return sprintf(
            '%s %s',
            $request->method(),
            $request->path()
        );
    }
}
