<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\EmployerController;
use App\Http\Controllers\Admin\ModeratorController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', DashboardController::class)
            ->middleware('admin.permission:dashboard.view')
            ->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::middleware('admin.permission:employers.view')->group(function () {
            Route::get('employers', [EmployerController::class, 'index'])->name('employers.index');
        });
        Route::middleware('admin.permission:employers.create')->group(function () {
            Route::get('employers/create', [EmployerController::class, 'create'])->name('employers.create');
            Route::post('employers', [EmployerController::class, 'store'])->name('employers.store');
        });
        Route::middleware('admin.permission:employers.view')->group(function () {
            Route::get('employers/{employer}', [EmployerController::class, 'show'])->name('employers.show');
        });
        Route::middleware('admin.permission:employers.manage')->group(function () {
            Route::post('employers/{employer}/assign-plan', [EmployerController::class, 'assignPlan'])
                ->name('employers.assign-plan');
            Route::patch('employers/{employer}/status', [EmployerController::class, 'updateStatus'])
                ->name('employers.update-status');
            Route::put('employers/{employer}/domains', [EmployerController::class, 'updateDomains'])
                ->name('employers.update-domains');
        });

        Route::middleware('admin.permission:candidates.view')->group(function () {
            Route::get('candidates', [CandidateController::class, 'index'])->name('candidates.index');
        });
        Route::middleware('admin.permission:candidates.create')->group(function () {
            Route::get('candidates/create', [CandidateController::class, 'create'])->name('candidates.create');
            Route::post('candidates', [CandidateController::class, 'store'])->name('candidates.store');
        });
        Route::middleware('admin.permission:candidates.view')->group(function () {
            Route::get('candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
        });
        Route::middleware('admin.permission:candidates.manage')->group(function () {
            Route::patch('candidates/{candidate}/status', [CandidateController::class, 'updateStatus'])
                ->name('candidates.update-status');
        });

        Route::middleware('admin.permission:settings.manage')->group(function () {
            Route::get('settings', [WebsiteSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [WebsiteSettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('admin.permission:domains.view')->group(function () {
            Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
        });
        Route::middleware('admin.permission:domains.manage')->group(function () {
            Route::get('domains/create', [DomainController::class, 'create'])->name('domains.create');
            Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
            Route::get('domains/{domain}/edit', [DomainController::class, 'edit'])->name('domains.edit');
            Route::put('domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
            Route::patch('domains/{domain}/toggle-status', [DomainController::class, 'toggleStatus'])
                ->name('domains.toggle-status');
            Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
            Route::get('domains/{domain}/settings', [DomainController::class, 'editSettings'])
                ->name('domains.settings.edit');
            Route::put('domains/{domain}/settings', [DomainController::class, 'updateSettings'])
                ->name('domains.settings.update');
        });
        Route::post('domains/switch-context', [DomainController::class, 'switchContext'])
            ->middleware('admin.auth')
            ->name('domains.switch-context');

        Route::middleware('admin.permission:plans.view')->group(function () {
            Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        });
        Route::middleware('admin.permission:plans.manage')->group(function () {
            Route::get('plans/create', [PlanController::class, 'create'])->name('plans.create');
            Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
            Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
            Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
            Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
            Route::patch('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])
                ->name('plans.toggle-status');
        });
        Route::middleware('admin.permission:plans.view')->group(function () {
            Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
        });

        Route::middleware('admin.permission:categories.view')->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        });
        Route::middleware('admin.permission:categories.manage')->group(function () {
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });
        Route::middleware('admin.permission:categories.review')->group(function () {
            Route::post('categories/{category}/approve', [CategoryController::class, 'approve'])
                ->name('categories.approve');
            Route::post('categories/{category}/reject', [CategoryController::class, 'reject'])
                ->name('categories.reject');
        });

        Route::middleware('admin.permission:roles.manage')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
        });

        Route::middleware('admin.permission:moderators.manage')->group(function () {
            Route::get('moderators', [ModeratorController::class, 'index'])->name('moderators.index');
            Route::get('moderators/create', [ModeratorController::class, 'create'])->name('moderators.create');
            Route::post('moderators', [ModeratorController::class, 'store'])->name('moderators.store');
            Route::get('moderators/{moderator}/edit', [ModeratorController::class, 'edit'])->name('moderators.edit');
            Route::put('moderators/{moderator}', [ModeratorController::class, 'update'])->name('moderators.update');
            Route::patch('moderators/{moderator}/status', [ModeratorController::class, 'toggleStatus'])
                ->name('moderators.toggle-status');
            Route::delete('moderators/{moderator}', [ModeratorController::class, 'destroy'])->name('moderators.destroy');
        });

        Route::middleware('admin.permission:activity.view')->group(function () {
            Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
            Route::get('activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');
        });
    });

});
