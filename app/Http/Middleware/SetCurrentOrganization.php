<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $orgId = $request->user()?->current_organization_id;

        $organization = $orgId
            ? Organization::withoutGlobalScopes()->find($orgId)
            : null;

        // Fallback: first org the user belongs to
        if (!$organization && $request->user()) {
            $organization = $request->user()->organizations()->first();
            if ($organization) {
                $request->user()->update(['current_organization_id' => $organization->id]);
            }
        }

        Organization::setCurrent($organization);

        Inertia::share('currentOrganization', $organization);
        Inertia::share('userOrganizations', fn () => $request->user()?->organizations()->get());

        return $next($request);
    }
}
