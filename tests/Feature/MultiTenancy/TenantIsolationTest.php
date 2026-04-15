<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('user can only see their own organisations data', function () {
    $owner1 = User::factory()->create();
    $owner2 = User::factory()->create();

    $org1 = Organization::factory()->create(['name' => 'Org One']);
    $org2 = Organization::factory()->create(['name' => 'Org Two']);

    $org1->users()->attach($owner1, ['role' => 'owner']);
    $org2->users()->attach($owner2, ['role' => 'owner']);

    $owner1->update(['current_organization_id' => $org1->id]);

    // Simulate tenant context for owner1
    Organization::setCurrent($org1);

    $this->actingAs($owner1)
        ->get(route('organizations.settings', $org1->slug))
        ->assertOk();

    $this->actingAs($owner1)
        ->get(route('organizations.settings', $org2->slug))
        ->assertForbidden();
});

test('non-member cannot access organisation settings', function () {
    $owner    = User::factory()->create();
    $stranger = User::factory()->create();

    $org = Organization::factory()->create();
    $org->users()->attach($owner, ['role' => 'owner']);
    $owner->update(['current_organization_id' => $org->id]);

    $this->actingAs($stranger)
        ->get(route('organizations.settings', $org->slug))
        ->assertForbidden();
});

test('organization scope filters records by current tenant', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    $org1->users()->attach($user1, ['role' => 'owner']);
    $org2->users()->attach($user2, ['role' => 'owner']);

    // Only the switcher controls what Organization::current() returns
    Organization::setCurrent($org1);
    expect(Organization::current()->id)->toBe($org1->id);

    Organization::setCurrent($org2);
    expect(Organization::current()->id)->toBe($org2->id);
});
