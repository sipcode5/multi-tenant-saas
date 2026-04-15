<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationService $service)
    {
    }

    public function create(): Response
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $organization = $this->service->createForUser($request->user(), $request->name);

        return redirect()->route('organizations.settings', $organization->slug)
            ->with('success', 'Organisation created.');
    }

    public function settings(Organization $organization): Response
    {
        $this->authorize('update', $organization);

        return Inertia::render('Organizations/Settings', [
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $organization->update($request->only('name'));

        return back()->with('success', 'Organisation updated.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        $request = request();
        $request->user()->update(['current_organization_id' => null]);

        return redirect()->route('dashboard');
    }
}
