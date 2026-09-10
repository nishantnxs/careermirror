<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasPermission
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $admin = $request->user('admin');

        if ($admin === null) {
            abort(401);
        }

        if ($permissions === [] || $admin->hasAnyPermission(...$permissions)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
