<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to switch workspace', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->post('/workspaces/switch', [
        'workspace_id' => $workspace->id,
    ]);

    $response->assertRedirect('/login');
});

it('validates workspace switch request input', function () {
    $user = User::factory()->create();

    // 1. Missing workspace_id
    $response = $this->actingAs($user)
        ->from('/dashboard')
        ->post('/workspaces/switch', []);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHasErrors(['workspace_id']);

    // 2. Non-integer workspace_id
    $response = $this->actingAs($user)
        ->from('/dashboard')
        ->post('/workspaces/switch', [
            'workspace_id' => 'not-an-integer',
        ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHasErrors(['workspace_id']);

    // 3. Non-existent workspace_id
    $response = $this->actingAs($user)
        ->from('/dashboard')
        ->post('/workspaces/switch', [
            'workspace_id' => 999999,
        ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHasErrors(['workspace_id']);
});

it('prevents user from switching to a workspace they are not a member of', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(); // User is not a member of this workspace

    $response = $this->actingAs($user)
        ->post('/workspaces/switch', [
            'workspace_id' => $workspace->id,
        ]);

    $response->assertForbidden();
});

it('switches the workspace successfully, sets the session, and redirects back', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => 'member']);

    $response = $this->actingAs($user)
        ->from('/dashboard')
        ->post('/workspaces/switch', [
            'workspace_id' => $workspace->id,
        ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('current_workspace_id', $workspace->id);
    $response->assertSessionHas('success', 'Workspace switched successfully.');
});
