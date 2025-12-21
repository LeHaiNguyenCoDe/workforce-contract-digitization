<?php

namespace App\Logging;

class ErrorBusinessTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_business', env('LOG_DAILY_DAYS', 30));
    }
}

