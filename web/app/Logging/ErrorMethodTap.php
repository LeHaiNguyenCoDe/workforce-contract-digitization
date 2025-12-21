<?php

namespace App\Logging;

class ErrorMethodTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_method', env('LOG_DAILY_DAYS', 30));
    }
}

