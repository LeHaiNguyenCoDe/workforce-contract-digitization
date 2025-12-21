<?php

namespace App\Logging;

class ErrorModelTap extends ErrorLogTap
{
    public function __construct()
    {
        parent::__construct('error_model', env('LOG_DAILY_DAYS', 30));
    }
}

