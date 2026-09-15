<?php

namespace App\Http\Controllers\Employer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.sign-in', [
            'activeTab' => 'employer',
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        Auth::guard('employer')->user()->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended(route('employer.dashboard'))
            ->with('success', 'Welcome back, '.Auth::guard('employer')->user()->name.'!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('employer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employer.login')->with('success', 'You have been logged out.');
    }
}
