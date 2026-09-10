<?php

namespace App\Http\Controllers\Candidate\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('candidate.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        Auth::guard('candidate')->user()->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended(route('candidate.dashboard'))
            ->with('success', 'Welcome back, '.Auth::guard('candidate')->user()->name.'!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('candidate')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('candidate.login')->with('success', 'You have been logged out.');
    }
}
