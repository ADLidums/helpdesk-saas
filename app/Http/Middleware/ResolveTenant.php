<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenantSlug');
        abort_unless(is_string($slug) && $slug !== '', 404);
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
