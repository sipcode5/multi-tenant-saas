<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class OrganizationService
{
    public function createForUser(User $user, string $name): Organization
    {
        $slug = $this->uniqueSlug($name);

        $organization = Organization::create([
            'name'   => $name,
            'slug'   => $slug,
            'status' => 'active',
        ]);

        $organization->users()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_organization_id' => $organization->id]);

        return $organization;
    }

    public function inviteMember(Organization $organization, User $inviter, string $email, string $role = 'member'): OrganizationInvitation
    {
        return $organization->invitations()->create([
            'invited_by' => $inviter->id,
            'email'      => $email,
            'role'       => $role,
            'token'      => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function acceptInvitation(OrganizationInvitation $invitation, User $user): void
    {
        $invitation->organization->users()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role],
        ]);

        $invitation->update(['accepted_at' => now()]);

        if (!$user->current_organization_id) {
            $user->update(['current_organization_id' => $invitation->organization_id]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Organization::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
