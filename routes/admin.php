<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('settings', [WebsiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [WebsiteSettingController::class, 'update'])->name('settings.update');

        Route::patch('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])
            ->name('plans.toggle-status');
        Route::resource('plans', PlanController::class);
    });

});
