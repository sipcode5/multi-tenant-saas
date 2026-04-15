<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationMemberController extends Controller
{
    public function __construct(private OrganizationService $service)
    {
    }

    public function index(Organization $organization): Response
    {
        $this->authorize('viewMembers', $organization);

        return Inertia::render('Organizations/Members', [
            'organization'     => $organization,
            'members'          => $organization->users()->get()->map(fn ($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'pivot' => ['role' => $u->pivot->role],
            ]),
            'invitations'      => $organization->invitations()
                ->where('accepted_at', null)
                ->where('expires_at', '>', now())
                ->get(['id', 'email', 'role', 'expires_at']),
            'canManageMembers' => request()->user()->can('manageMembers', $organization),
        ]);
    }

    public function invite(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'in:admin,member'],
        ]);

        $this->service->inviteMember($organization, $request->user(), $request->email, $request->role);

        return back()->with('success', 'Invitation sent.');
    }

    public function acceptInvitation(string $token): RedirectResponse
    {
        $invitation = OrganizationInvitation::where('token', $token)
            ->where('accepted_at', null)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $this->service->acceptInvitation($invitation, auth()->user());

        return redirect()->route('dashboard')->with('success', "You joined {$invitation->organization->name}.");
    }

    public function updateRole(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        $request->validate(['role' => ['required', 'in:admin,member']]);

        $organization->users()->updateExistingPivot($user->id, ['role' => $request->role]);

        return back()->with('success', 'Role updated.');
    }

    public function remove(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        if ($organization->users()->where('users.id', $user->id)->wherePivot('role', 'owner')->exists()) {
            return back()->with('error', 'Cannot remove the owner.');
        }

        $organization->users()->detach($user->id);

        if ($user->current_organization_id === $organization->id) {
            $user->update(['current_organization_id' => null]);
        }

        return back()->with('success', 'Member removed.');
    }
}
