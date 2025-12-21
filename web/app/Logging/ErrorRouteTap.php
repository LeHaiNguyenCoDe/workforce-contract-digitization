<?php

namespace App\Logging;

class ErrorRouteTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_route', env('LOG_DAILY_DAYS', 30));
    }
}

