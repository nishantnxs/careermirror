<?php

namespace App\Http\Requests\Concerns;

use App\Enums\AccountStatus;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait AuthenticatesAccount
{
    abstract protected function guardName(): string;

    /**
     * Attempt to log the account in, throttling repeated failures by email + IP.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $credentials['status'] = AccountStatus::Active->value;

        if (! Auth::guard($this->guardName())->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => $this->failedAuthenticationMessage(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function failedAuthenticationMessage(): string
    {
        $account = Auth::guard($this->guardName())->getProvider()->retrieveByCredentials(
            $this->only('email')
        );

        if ($account && method_exists($account, 'canAuthenticate') && ! $account->canAuthenticate()) {
            return $account->status->authenticationErrorMessage();
        }

        return 'These credentials do not match our records.';
    }

    /** @throws ValidationException */
    protected function ensureIsNotRateLimited(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            event(new Lockout($this));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(
            $this->guardName().'|'.Str::lower((string) $this->input('email')).'|'.$this->ip()
        );
    }
}
