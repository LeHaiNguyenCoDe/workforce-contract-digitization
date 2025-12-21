<?php

namespace App\Logging;

class ErrorChannelResolver
{
    /**
     * Get error channel name based on exception type
     *
     * @param  \Throwable  $exception
     * @return string
     */
    public static function getChannel(\Throwable $exception): string
    {
        $class = get_class($exception);
        
        // Database errors
        if (str_contains($class, 'QueryException') || 
            str_contains($class, 'PDOException') ||
            str_contains($class, 'DatabaseException')) {
            return 'error_database';
        }
        
        // Authentication errors
        if (str_contains($class, 'AuthenticationException') ||
            str_contains($class, 'UnauthorizedException')) {
            return 'error_auth';
        }
        
        // Validation errors
        if (str_contains($class, 'ValidationException')) {
            return 'error_validation';
        }
        
        // Route errors
        if (str_contains($class, 'RouteNotFoundException') ||
            str_contains($class, 'NotFoundHttpException') ||
            str_contains($class, 'MethodNotAllowedHttpException')) {
            return 'error_route';
        }
        
        // Model not found
        if (str_contains($class, 'ModelNotFoundException')) {
            return 'error_model';
        }
        
        // Business logic errors
        if (str_contains($class, 'BusinessLogicException')) {
            return 'error_business';
        }
        
        // Method not found
        if (str_contains($class, 'BadMethodCallException')) {
            return 'error_method';
        }
        
        // General exception
        return 'error_general';
    }
}

