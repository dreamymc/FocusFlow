<?php

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__ . '/../../Support/WorkspaceHelpers.php';

use function Tests\Support\attachWithRole;

it('renders workspace creation page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/workspaces/create');

    $response->assertOk();
});

it('creates a workspace via web form and redirects to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/workspaces', [
            'name' => 'My Web Workspace',
        ]);

    $workspace = Workspace::where('name', 'My Web Workspace')->first();
    expect($workspace)->not->toBeNull();

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Admin->value,
    ]);

    expect(session('current_workspace_id'))->toBe($workspace->id);
});

it('allows admins to view settings', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    attachWithRole($workspace, $user, WorkspaceRole::Admin);

    $response = $this->actingAs($user)
        ->get(route('workspaces.settings', $workspace));

    $response->assertOk();
});

it('denies members and viewers from settings', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    
    // Member
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Member->value]);
    $response = $this->actingAs($user)
        ->get(route('workspaces.settings', $workspace));
    $response->assertForbidden();

    // Viewer
    $workspace->users()->updateExistingPivot($user->id, ['role' => WorkspaceRole::Viewer->value]);
    $response = $this->actingAs($user)
        ->get(route('workspaces.settings', $workspace));
    $response->assertForbidden();
});

it('allows admins to update workspace name', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['name' => 'Old Name']);
    attachWithRole($workspace, $user, WorkspaceRole::Admin);

    $response = $this->actingAs($user)
        ->from(route('workspaces.settings', $workspace))
        ->put("/workspaces/{$workspace->id}", [
            'name' => 'New Name',
        ]);

    $response->assertRedirect(route('workspaces.settings', $workspace));
    $workspace->refresh();
    expect($workspace->name)->toBe('New Name');
});

it('allows admins to invite members via web form', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    attachWithRole($workspace, $user, WorkspaceRole::Admin);

    $response = $this->actingAs($user)
        ->from(route('workspaces.settings', $workspace))
        ->post("/workspaces/{$workspace->id}/invite", [
            'email' => 'newuser@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);

    $response->assertRedirect(route('workspaces.settings', $workspace));
    $this->assertDatabaseHas('invitations', [
        'workspace_id' => $workspace->id,
        'email' => 'newuser@example.com',
        'role' => WorkspaceRole::Member->value,
    ]);
});
