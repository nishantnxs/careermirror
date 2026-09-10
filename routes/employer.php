<?php

use App\Http\Controllers\Employer\Auth\LoginController;
use App\Http\Controllers\Employer\Auth\RegisterController;
use App\Http\Controllers\Employer\CategoryController;
use App\Http\Controllers\Employer\DashboardController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Employer\OrderController;
use App\Http\Controllers\Employer\PaymentController;
use App\Http\Controllers\Employer\PlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('employer')->name('employer.')->group(function () {
    Route::middleware('employer.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');
    });

    Route::middleware('employer.auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/{plan}/checkout', [PlanController::class, 'checkout'])->name('plans.checkout');
        Route::post('plans/{plan}/purchase', [PlanController::class, 'purchase'])->name('plans.purchase');

        Route::get('payments/fake/{order}/complete', [PaymentController::class, 'fakeComplete'])
            ->name('payments.fake.complete');
        Route::post('payments/razorpay/{order}/confirm', [PaymentController::class, 'razorpayConfirm'])
            ->name('payments.razorpay.confirm');
        Route::get('payments/stripe/{order}/success', [PaymentController::class, 'stripeSuccess'])
            ->name('payments.stripe.success');
        Route::get('payments/stripe/{order}/cancel', [PaymentController::class, 'stripeCancel'])
            ->name('payments.stripe.cancel');
        Route::get('payments/paypal/{order}/return', [PaymentController::class, 'paypalReturn'])
            ->name('payments.paypal.return');
        Route::get('payments/paypal/{order}/cancel', [PaymentController::class, 'paypalCancel'])
            ->name('payments.paypal.cancel');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('categories/check', [CategoryController::class, 'check'])->name('categories.check');
        Route::middleware('employer.subscribed')->group(function () {
            Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
            Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        });
        Route::get('jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::put('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
    });
});
