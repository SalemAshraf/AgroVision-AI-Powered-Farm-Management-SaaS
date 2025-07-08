<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    public function handle($request, Closure $next)
{
    if (auth('sanctum')->check() && auth('sanctum')->user()->role === 'admin') {
        return $next($request);
    }

    return redirect('/')->with('error', 'Access denied. Admins only.');
}

}
