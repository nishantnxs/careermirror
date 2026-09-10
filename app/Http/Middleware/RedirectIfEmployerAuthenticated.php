<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfEmployerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('employer')->check()) {
            return redirect()->route('employer.dashboard');
        }

        return $next($request);
    }
}
