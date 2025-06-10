<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if ($request->user()->role !== $role) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Auth::check() && $request->cookie('remember_token')) {
            $user = User::where('remember_token', $request->cookie('remember_token'))->first();

            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
