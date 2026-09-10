<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\CandidateAuthenticate;
use App\Http\Middleware\EmployerAuthenticate;
use App\Http\Middleware\EnsureAdminHasPermission;
use App\Http\Middleware\EnsureDomainIsActive;
use App\Http\Middleware\EnsureEmployerHasActiveSubscription;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfCandidateAuthenticated;
use App\Http\Middleware\RedirectIfEmployerAuthenticated;
use App\Http\Middleware\ResolveDomain;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/candidate.php'));
            Route::middleware('web')->group(base_path('routes/employer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', [
            ResolveDomain::class,
            EnsureDomainIsActive::class,
        ]);

        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
            'admin.permission' => EnsureAdminHasPermission::class,
            'candidate.auth' => CandidateAuthenticate::class,
            'candidate.guest' => RedirectIfCandidateAuthenticated::class,
            'employer.auth' => EmployerAuthenticate::class,
            'employer.guest' => RedirectIfEmployerAuthenticated::class,
            'employer.subscribed' => EnsureEmployerHasActiveSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
