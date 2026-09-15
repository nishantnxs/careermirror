<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('candidate.settings.edit', [
            'candidate' => $request->user('candidate'),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $candidate = $request->user('candidate');

        $candidate->update([
            'password' => $request->string('password')->toString(),
        ]);

        return redirect()
            ->route('candidate.settings.edit')
            ->with('success', 'Your password has been changed.');
    }
}
