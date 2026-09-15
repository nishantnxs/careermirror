<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\Candidate;
use App\Models\Employer;
use App\Support\GoogleUserProfile;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthenticationService
{
    public const SESSION_STATE_KEY = 'google_oauth_state';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function authorizationUrl(string $callbackUrl): string
    {
        $state = Str::random(40);
        session([self::SESSION_STATE_KEY => $state]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $callbackUrl,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ]);
    }

    public function userFromCallback(Request $request, string $callbackUrl): GoogleUserProfile
    {
        if ($request->filled('error')) {
            throw ValidationException::withMessages([
                'email' => $request->string('error')->toString() === 'access_denied'
                    ? 'Google sign-in was cancelled.'
                    : 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        $state = $request->string('state')->toString();
        $expected = (string) $request->session()->pull(self::SESSION_STATE_KEY);

        if ($state === '' || $expected === '' || ! hash_equals($expected, $state)) {
            throw ValidationException::withMessages([
                'email' => 'Google sign-in could not be verified. Please try again.',
            ]);
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            throw ValidationException::withMessages([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        try {
            $tokenResponse = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => $callbackUrl,
                    'grant_type' => 'authorization_code',
                ])
                ->throw();
        } catch (RequestException) {
            throw ValidationException::withMessages([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw ValidationException::withMessages([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        try {
            $profileResponse = Http::withToken($accessToken)
                ->timeout(10)
                ->acceptJson()
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->throw();
        } catch (RequestException) {
            throw ValidationException::withMessages([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        $id = $profileResponse->json('sub');
        $email = $profileResponse->json('email');
        $name = $profileResponse->json('name') ?: $profileResponse->json('given_name');

        if (! is_string($id) || $id === '' || ! is_string($email) || $email === '') {
            throw ValidationException::withMessages([
                'email' => 'Google did not return a usable email address.',
            ]);
        }

        return new GoogleUserProfile(
            id: $id,
            name: is_string($name) && $name !== '' ? $name : 'Google User',
            email: $email,
        );
    }

    /**
     * @return array{account: Candidate, created: bool}
     */
    public function resolveCandidate(GoogleUserProfile $profile): array
    {
        $candidate = Candidate::query()->where('google_id', $profile->id)->first()
            ?? Candidate::query()->where('email', $profile->email)->first();

        if ($candidate === null) {
            $candidate = Candidate::query()->create([
                'name' => $profile->name,
                'email' => $profile->email,
                'google_id' => $profile->id,
                'password' => Str::password(32),
                'status' => AccountStatus::Active,
            ]);

            return ['account' => $candidate, 'created' => true];
        }

        $this->assertCanAuthenticate($candidate);
        $this->assertGoogleIdCanBeLinked($candidate->google_id, $profile->id);

        if ($candidate->google_id === null) {
            $candidate->forceFill(['google_id' => $profile->id])->save();
        }

        return ['account' => $candidate, 'created' => false];
    }

    /**
     * @return array{account: Employer, created: bool}
     */
    public function resolveEmployer(GoogleUserProfile $profile): array
    {
        $employer = Employer::query()->where('google_id', $profile->id)->first()
            ?? Employer::query()->where('email', $profile->email)->first();

        if ($employer === null) {
            $employer = Employer::query()->create([
                'name' => $profile->name,
                'company_name' => $profile->name,
                'email' => $profile->email,
                'google_id' => $profile->id,
                'password' => Str::password(32),
                'status' => AccountStatus::Active,
            ]);
            $employer->grantActiveDomains();

            return ['account' => $employer, 'created' => true];
        }

        $this->assertCanAuthenticate($employer);
        $this->assertGoogleIdCanBeLinked($employer->google_id, $profile->id);

        if ($employer->google_id === null) {
            $employer->forceFill(['google_id' => $profile->id])->save();
        }

        return ['account' => $employer, 'created' => false];
    }

    private function assertCanAuthenticate(Candidate|Employer $account): void
    {
        if (! $account->canAuthenticate()) {
            throw ValidationException::withMessages([
                'email' => $account->status->authenticationErrorMessage(),
            ]);
        }
    }

    private function assertGoogleIdCanBeLinked(?string $existingGoogleId, string $incomingGoogleId): void
    {
        if ($existingGoogleId !== null && $existingGoogleId !== $incomingGoogleId) {
            throw ValidationException::withMessages([
                'email' => 'This email is already linked to a different Google account.',
            ]);
        }
    }
}
