<?php

namespace App\Http\Controllers\Candidate\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    public function __construct(public GoogleAuthenticationService $google) {}

    public function redirect(): RedirectResponse
    {
        if (! $this->google->isConfigured()) {
            return redirect()->route('candidate.login')
                ->withErrors(['email' => 'Google sign-in is not configured.']);
        }

        return redirect()->away(
            $this->google->authorizationUrl(route('candidate.auth.google.callback'))
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $profile = $this->google->userFromCallback(
                $request,
                route('candidate.auth.google.callback'),
            );
            $result = $this->google->resolveCandidate($profile);
        } catch (ValidationException $exception) {
            return redirect()->route('candidate.login')->withErrors($exception->errors());
        } catch (\Throwable) {
            return redirect()->route('candidate.login')
                ->withErrors(['email' => 'Google sign-in could not be completed. Please try again.']);
        }

        Auth::guard('candidate')->login($result['account']);

        $request->session()->regenerate();

        $result['account']->forceFill(['last_login_at' => now()])->save();

        $message = $result['created']
            ? 'Your candidate account has been created.'
            : 'Welcome back, '.$result['account']->name.'!';

        return redirect()->intended(route('candidate.dashboard'))->with('success', $message);
    }
}
