<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('activity log page is accessible', function () {
    $user = User::factory()->create();
    $org  = Organization::factory()->create();
    $org->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_organization_id' => $org->id]);
    Organization::setCurrent($org);

    $this->actingAs($user)
        ->get(route('activity-log.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('ActivityLog/Index'));
});

test('activity is logged when organisation is updated', function () {
    $user = User::factory()->create();
    $org  = Organization::factory()->create(['name' => 'Before']);
    $org->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_organization_id' => $org->id]);
    Organization::setCurrent($org);

    $this->actingAs($user)
        ->patch(route('organizations.update', $org->slug), ['name' => 'After'])
        ->assertRedirect();

    expect($org->fresh()->name)->toBe('After');
});
