<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCandidateAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('candidate')->check()) {
            return redirect()->route('candidate.dashboard');
        }

        return $next($request);
    }
}
