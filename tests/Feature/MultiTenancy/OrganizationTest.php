<?php

use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('registering creates an organisation for the user', function () {
    $response = $this->post(route('register'), [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->organizations)->toHaveCount(1);
    expect($user->currentOrganization)->not->toBeNull();
    expect($user->currentOrganization->users()->wherePivot('role', 'owner')->first()->id)
        ->toBe($user->id);
});

test('user can create additional organisations', function () {
    $user = User::factory()->create();
    $service = app(OrganizationService::class);

    $org1 = $service->createForUser($user, 'First Org');
    $org2 = $service->createForUser($user, 'Second Org');

    expect($user->fresh()->organizations)->toHaveCount(2);
    expect($user->fresh()->current_organization_id)->toBe($org2->id);
});

test('organisation members index is accessible to members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $org = Organization::factory()->create();
    $org->users()->attach($owner, ['role' => 'owner']);
    $org->users()->attach($member, ['role' => 'member']);

    $owner->update(['current_organization_id' => $org->id]);
    Organization::setCurrent($org);

    $this->actingAs($owner)
        ->get(route('organizations.members.index', $org->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Organizations/Members'));
});

test('user can switch organisations', function () {
    $user = User::factory()->create();
    $service = app(OrganizationService::class);

    $org1 = $service->createForUser($user, 'Org One');
    $org2 = $service->createForUser($user, 'Org Two');

    $this->actingAs($user)
        ->post(route('organizations.switch'), ['organization_id' => $org1->id])
        ->assertRedirect();

    expect($user->fresh()->current_organization_id)->toBe($org1->id);
});
