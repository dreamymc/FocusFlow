<?php

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use Illuminate\Support\Facades\Gate;

it('blocks member invites beyond 3 on the free plan', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Admin->value]);

    $workspace->users()->attach(User::factory(2)->create(), ['role' => WorkspaceRole::Member->value]);

    expect(Gate::forUser($user)->allows('workspace.pro', $workspace))->toBeFalse();
});

it('allows member invites beyond 3 on the pro plan', function () {
    $workspace = Workspace::factory()->create();
    $workspace->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);
    
    $user = User::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Admin->value]);

    $workspace->users()->attach(User::factory(2)->create(), ['role' => WorkspaceRole::Member->value]);

    expect(Gate::forUser($user)->allows('workspace.pro', $workspace))->toBeTrue();
});
