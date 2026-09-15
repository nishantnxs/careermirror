<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('candidate.profile.edit', [
            'candidate' => $request->user('candidate'),
            'jobTypes' => [
                'full-time' => 'Full-time',
                'part-time' => 'Part-time',
                'contract' => 'Contract',
                'internship' => 'Internship',
                'freelance' => 'Freelance',
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $candidate = $request->user('candidate');
        $data = $request->profileData();

        if ($request->boolean('remove_avatar') && $candidate->avatar_path) {
            Storage::disk('public')->delete($candidate->avatar_path);
            $data['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($candidate->avatar_path) {
                Storage::disk('public')->delete($candidate->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('avatars/candidates/'.$candidate->id, 'public');
        }

        $candidate->update($data);

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Your profile has been updated.');
    }
}
