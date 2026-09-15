<?php

namespace App\Http\Controllers\Candidate\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\RegisterRequest;
use App\Models\Candidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.create-account', [
            'activeTab' => 'candidate',
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $candidate = Candidate::create($request->validated());

        Auth::guard('candidate')->login($candidate);

        $request->session()->regenerate();

        $candidate->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('candidate.dashboard')
            ->with('success', 'Your candidate account has been created.');
    }
}
