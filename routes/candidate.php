<?php

use App\Http\Controllers\Candidate\Auth\LoginController;
use App\Http\Controllers\Candidate\Auth\RegisterController;
use App\Http\Controllers\Candidate\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('candidate')->name('candidate.')->group(function () {
    Route::middleware('candidate.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');
    });

    Route::middleware('candidate.auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    });
});
