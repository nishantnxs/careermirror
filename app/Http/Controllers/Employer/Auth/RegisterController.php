<?php

namespace App\Http\Controllers\Employer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\RegisterRequest;
use App\Models\Employer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.create-account', [
            'activeTab' => 'employer',
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $employer = Employer::create($request->validated());
        $employer->grantActiveDomains();

        Auth::guard('employer')->login($employer);

        $request->session()->regenerate();

        $employer->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('employer.dashboard')
            ->with('success', 'Your employer account has been created.');
    }
}
