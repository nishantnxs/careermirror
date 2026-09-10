<?php

namespace App\Http\Middleware;

use App\Services\DomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDomain
{
    public function __construct(public DomainService $domains) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Admin uses its own context switcher; do not bind host domain there.
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $domain = $this->domains->resolveFromRequest($request);
        $this->domains->bind($domain);

        return $next($request);
    }
}
