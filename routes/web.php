<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/jobs', [JobBoardController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobBoardController::class, 'show'])->name('jobs.show');
