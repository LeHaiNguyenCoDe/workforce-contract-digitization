<?php

namespace App\Logging;

class ErrorValidationTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_validation', env('LOG_DAILY_DAYS', 30));
    }
}

