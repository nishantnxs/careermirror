<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CandidateAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('candidate');

        if (! $guard->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return redirect()->guest(route('candidate.login'));
        }

        if (! $guard->user()->canAuthenticate()) {
            $message = $guard->user()->status->authenticationErrorMessage();
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('candidate.login')
                ->withErrors(['email' => $message]);
        }

        Auth::shouldUse('candidate');

        return $next($request);
    }
}
