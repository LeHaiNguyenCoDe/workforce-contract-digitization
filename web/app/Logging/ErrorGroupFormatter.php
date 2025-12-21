<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class ErrorGroupFormatter extends LineFormatter
{
    /**
     * Formats a log record.
     *
     * @param  LogRecord  $record
     * @return string
     */
    public function format(LogRecord $record): string
    {
        $output = parent::format($record);
        
        // Group errors by type
        $message = $record->message;
        $context = $record->context;
        
        // Extract error type from exception
        $errorType = 'UNKNOWN';
        $errorSummary = $message;
        
        if (isset($context['exception'])) {
            $exception = $context['exception'];
            if ($exception instanceof \Exception || $exception instanceof \Throwable) {
                $errorType = $this->categorizeError($exception);
                $errorSummary = $this->extractErrorSummary($exception);
            }
        }
        
        // Add error grouping header
        $groupHeader = sprintf(
            "\n========== [ERROR GROUP: %s] [%s] %s ==========\n",
            $errorType,
            $record->datetime->format('Y-m-d H:i:s'),
            $errorSummary
        );
        
        return $groupHeader . $output;
    }
    
    /**
     * Categorize error by exception type
     *
     * @param  \Exception  $exception
     * @return string
     */
    private function categorizeError(\Exception $exception): string
    {
        $class = get_class($exception);
        
        // Route errors
        if (str_contains($class, 'RouteNotFoundException')) {
            return 'ROUTE_NOT_FOUND';
        }
        
        // Database errors
        if (str_contains($class, 'QueryException') || str_contains($class, 'PDOException')) {
            return 'DATABASE_ERROR';
        }
        
        // Authentication errors
        if (str_contains($class, 'AuthenticationException')) {
            return 'AUTH_ERROR';
        }
        
        // Validation errors
        if (str_contains($class, 'ValidationException')) {
            return 'VALIDATION_ERROR';
        }
        
        // Model not found
        if (str_contains($class, 'ModelNotFoundException')) {
            return 'MODEL_NOT_FOUND';
        }
        
        // Method not found
        if (str_contains($class, 'BadMethodCallException')) {
            return 'METHOD_NOT_FOUND';
        }
        
        // General exception
        return 'GENERAL_ERROR';
    }
    
    /**
     * Extract error summary
     *
     * @param  \Exception  $exception
     * @return string
     */
    private function extractErrorSummary(\Exception $exception): string
    {
        $message = $exception->getMessage();
        
        // Extract key information
        if (preg_match('/Route \[(\w+)\] not defined/', $message, $matches)) {
            return "Route '{$matches[1]}' not defined";
        }
        
        if (preg_match('/relation "(\w+)" does not exist/', $message, $matches)) {
            return "Table '{$matches[1]}' does not exist";
        }
        
        if (preg_match('/Call to undefined method (.+?)::(.+?)\(\)/', $message, $matches)) {
            return "Method '{$matches[2]}' not found in {$matches[1]}";
        }
        
        // Return first 100 characters of message
        return mb_substr($message, 0, 100);
    }
}

