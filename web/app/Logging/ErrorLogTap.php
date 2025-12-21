<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

class ErrorLogTap
{
    protected string $filename;
    protected int $maxFiles;

    public function __construct(string $filename, int $maxFiles = 30)
    {
        $this->filename = $filename;
        $this->maxFiles = $maxFiles;
    }

    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof \Monolog\Handler\RotatingFileHandler) {
                // Get the current path and extract base filename
                $reflection = new \ReflectionClass($handler);
                $urlProperty = $reflection->getProperty('url');
                $urlProperty->setAccessible(true);
                $currentPath = $urlProperty->getValue($handler);
                
                // Create daily folder structure
                $date = date('Y-m-d');
                $logDir = storage_path('logs/' . $date);
                
                // Create directory if it doesn't exist
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                
                // Set new path with daily folder
                $newPath = $logDir . '/' . $this->filename . '.log';
                $urlProperty->setValue($handler, $newPath);
            }
        }
    }
}

