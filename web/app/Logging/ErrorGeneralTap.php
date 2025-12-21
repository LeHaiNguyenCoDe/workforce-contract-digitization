<?php

namespace App\Logging;

class ErrorGeneralTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_general', env('LOG_DAILY_DAYS', 30));
    }
}

