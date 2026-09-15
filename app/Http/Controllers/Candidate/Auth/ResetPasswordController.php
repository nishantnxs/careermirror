<?php

namespace App\Http\Controllers\Candidate\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('candidate.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker('candidates')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Candidate $candidate, string $password): void {
                $candidate->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($candidate));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('candidate.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]])->onlyInput('email');
    }
}
