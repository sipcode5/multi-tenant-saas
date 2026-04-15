<?php

use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\PlanSeeder::class);
});

test('billing index page loads for authenticated user', function () {
    $user = User::factory()->create();
    $org  = Organization::factory()->create();
    $org->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_organization_id' => $org->id]);
    Organization::setCurrent($org);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
            ->component('Billing/Index')
            ->has('plans')
            ->where('plans.0.name', 'Starter')
        );
});

test('billing page shows all plans', function () {
    $user = User::factory()->create();
    $org  = Organization::factory()->create();
    $org->users()->attach($user, ['role' => 'owner']);
    $user->update(['current_organization_id' => $org->id]);
    Organization::setCurrent($org);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertInertia(fn ($page) => $page->has('plans', 3));
});
