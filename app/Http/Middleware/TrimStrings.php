<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimStrings
{
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function handle(Request $request, \Closure $next): Response
    {
        return $next($request);
    }
}
