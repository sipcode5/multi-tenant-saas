<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $org = Organization::current();

        if ($org && $org->isSuspended()) {
            return redirect()->route('billing.index')
                ->with('error', 'Your organisation is suspended. Please update your billing.');
        }

        return $next($request);
    }
}
