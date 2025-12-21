<?php

namespace App\Logging;

class ErrorAuthTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_auth', env('LOG_DAILY_DAYS', 30));
    }
}

