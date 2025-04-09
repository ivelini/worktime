<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserTypeMiddleware
{
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        return in_array(Auth::user()->type, $types)
            ? $next($request)
            : redirect('/login');
    }
}
