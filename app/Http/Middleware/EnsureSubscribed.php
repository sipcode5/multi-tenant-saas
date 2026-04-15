<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $org = Organization::current();

        if (!$org || !$org->subscribed('default')) {
            return redirect()->route('billing.index')
                ->with('error', 'This feature requires an active subscription.');
        }

        return $next($request);
    }
}
