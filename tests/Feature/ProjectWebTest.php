<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Enums\WorkspaceRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__ . '/../Support/WorkspaceHelpers.php';

use function Tests\Support\attachWithRole;

it('renders projects index page for workspace members', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Member->value]);

    $response = $this->actingAs($user)
        ->get("/workspaces/{$workspace->id}/projects");

    $response->assertOk();
});

it('allows admins and members to create a project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    attachWithRole($workspace, $user, WorkspaceRole::Member);

    $response = $this->actingAs($user)
        ->from("/workspaces/{$workspace->id}/projects")
        ->post("/workspaces/{$workspace->id}/projects", [
            'name' => 'New Web Project',
            'description' => 'A project description',
        ]);

    $response->assertRedirect("/workspaces/{$workspace->id}/projects");
    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace->id,
        'name' => 'New Web Project',
    ]);
});

it('denies viewers from creating projects', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Viewer->value]);

    $response = $this->actingAs($user)
        ->post("/workspaces/{$workspace->id}/projects", [
            'name' => 'Should Fail Project',
        ]);

    $response->assertForbidden();
});
