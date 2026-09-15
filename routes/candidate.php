<?php

use App\Http\Controllers\Candidate\AccountSettingsController;
use App\Http\Controllers\Candidate\Auth\ForgotPasswordController;
use App\Http\Controllers\Candidate\Auth\GoogleAuthController;
use App\Http\Controllers\Candidate\Auth\LoginController;
use App\Http\Controllers\Candidate\Auth\RegisterController;
use App\Http\Controllers\Candidate\Auth\ResetPasswordController;
use App\Http\Controllers\Candidate\DashboardController;
use App\Http\Controllers\Candidate\JobApplicationController;
use App\Http\Controllers\Candidate\JobController;
use App\Http\Controllers\Candidate\MessageController;
use App\Http\Controllers\Candidate\ProfileController;
use App\Http\Controllers\Candidate\ResumeController;
use App\Http\Controllers\Candidate\SavedJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('candidate')->name('candidate.')->group(function () {
    Route::middleware('candidate.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
        Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');

        Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
    });

    Route::middleware('candidate.auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('settings', [AccountSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password');

        Route::get('resumes', [ResumeController::class, 'index'])->name('resumes.index');
        Route::get('resumes/create', [ResumeController::class, 'create'])->name('resumes.create');
        Route::post('resumes', [ResumeController::class, 'store'])->name('resumes.store');
        Route::get('resumes/upload', [ResumeController::class, 'uploadForm'])->name('resumes.upload');
        Route::post('resumes/upload', [ResumeController::class, 'upload'])->name('resumes.upload.store');
        Route::get('resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('resumes.edit');
        Route::put('resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
        Route::get('resumes/{resume}/preview', [ResumeController::class, 'preview'])->name('resumes.preview');
        Route::get('resumes/{resume}/download', [ResumeController::class, 'download'])->name('resumes.download');
        Route::patch('resumes/{resume}/default', [ResumeController::class, 'makeDefault'])->name('resumes.default');
        Route::delete('resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');

        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');

        Route::get('saved-jobs', [SavedJobController::class, 'index'])->name('saved-jobs.index');
        Route::post('jobs/{job}/save', [SavedJobController::class, 'store'])->name('jobs.save');
        Route::delete('jobs/{job}/save', [SavedJobController::class, 'destroy'])->name('jobs.unsave');

        Route::get('applications', [JobApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [JobApplicationController::class, 'show'])->name('applications.show');
        Route::post('applications/{application}/withdraw', [JobApplicationController::class, 'withdraw'])
            ->name('applications.withdraw');

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread');
        Route::get('messages/{conversation}/updates', [MessageController::class, 'updates'])->name('messages.updates');
        Route::get('messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    });
});
