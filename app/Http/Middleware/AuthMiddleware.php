<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     * Checks if user is logged in (session exists).
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Please log in to continue.');
        }

        return $next($request);
    }
}