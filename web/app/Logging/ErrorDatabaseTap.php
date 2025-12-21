<?php

namespace App\Logging;

class ErrorDatabaseTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_database', env('LOG_DAILY_DAYS', 30));
    }
}

