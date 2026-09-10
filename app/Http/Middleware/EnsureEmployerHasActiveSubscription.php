<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployerHasActiveSubscription
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $employer = $request->user('employer');

        $subscription = $employer?->subscriptions()->active()->latest('id')->first();

        if ($subscription === null || ! $subscription->canPostJob()) {
            return redirect()
                ->route('employer.plans.index')
                ->with('error', 'Buy or activate a plan with remaining job credits before posting a job.');
        }

        return $next($request);
    }
}
