<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchOrganizationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['organization_id' => ['required', 'integer']]);

        $organization = Organization::withoutGlobalScopes()
            ->whereHas('users', fn ($q) => $q->where('users.id', $request->user()->id))
            ->findOrFail($request->organization_id);

        $request->user()->update(['current_organization_id' => $organization->id]);

        return redirect()->intended(route('dashboard'));
    }
}
