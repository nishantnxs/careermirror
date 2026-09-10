<?php

namespace App\Http\Middleware;

use App\Services\DomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDomainIsActive
{
    public function __construct(public DomainService $domains) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*') || $request->is('up')) {
            return $next($request);
        }

        $domain = $this->domains->current();

        if ($domain === null) {
            abort(503, 'No website domain is configured for this host.');
        }

        if (! $domain->isActive()) {
            abort(503, 'This website is temporarily unavailable.');
        }

        return $next($request);
    }
}
