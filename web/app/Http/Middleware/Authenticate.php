<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Đối với API, ta không redirect sang trang login mà để Laravel
        // trả về response 401 (Unauthorized). Việc trả về null ở đây sẽ
        // tránh lỗi "Route [getLogin] not defined" khi gọi các endpoint API.
        return null;
    }
}
