<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Enums\WorkspaceRole;
use Illuminate\Testing\Fluent\AssertableJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows admin and member to create a project, but denies viewer', function () {
    // 1. Admin test
    [$workspace, $admin] = createWorkspaceWithUser(WorkspaceRole::Admin);
    
    $response = $this->actingAs($admin)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects", [
            'name' => 'Admin Project',
            'description' => 'Created by Admin',
        ]);

    $response->assertCreated()
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('data.id')
                 ->where('data.name', 'Admin Project')
                 ->where('data.description', 'Created by Admin')
                 ->etc()
        );

    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace->id,
        'name' => 'Admin Project',
    ]);

    // 2. Member test
    [$workspace2, $member] = createWorkspaceWithUser(WorkspaceRole::Member);

    $response = $this->actingAs($member)
        ->postJson("/api/v1/workspaces/{$workspace2->id}/projects", [
            'name' => 'Member Project',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace2->id,
        'name' => 'Member Project',
    ]);

    // 3. Viewer test
    [$workspace3, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);

    $response = $this->actingAs($viewer)
        ->postJson("/api/v1/workspaces/{$workspace3->id}/projects", [
            'name' => 'Viewer Project',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('projects', [
        'name' => 'Viewer Project',
    ]);
});

it('validates project creation name is required', function () {
    [$workspace, $admin] = createWorkspaceWithUser(WorkspaceRole::Admin);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects", [
            'description' => 'Missing name',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('lists projects for workspace members, but denies non-members', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    
    $project1 = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project One',
    ]);

    $project2 = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project Two',
    ]);

    // Member lists projects
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('data.0.id')
                 ->where('data.0.name', 'Project One')
                 ->where('data.1.name', 'Project Two')
                 ->etc()
        );

    // Non-member lists projects
    $stranger = User::factory()->create();
    $response = $this->actingAs($stranger)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects");

    $response->assertStatus(403);
});

it('shows a project to workspace members, but denies non-members', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Target Project',
    ]);

    // Member views project
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}");

    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('data.id', $project->id)
                 ->where('data.name', 'Target Project')
                 ->etc()
        );

    // Non-member views project
    $stranger = User::factory()->create();
    $response = $this->actingAs($stranger)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}");

    $response->assertStatus(403);

    // Accessing project with mismatching workspace returns 404
    $otherWorkspace = Workspace::factory()->create();
    $otherWorkspace->users()->attach($member, ['role' => 'member']);
    
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$otherWorkspace->id}/projects/{$project->id}");

    $response->assertStatus(404);
});

it('allows admin and member to update project, but denies viewer', function () {
    // 1. Member update
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Original Name',
    ]);

    $response = $this->actingAs($member)
        ->putJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}", [
            'name' => 'Updated by Member',
            'description' => 'New Description',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated by Member');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated by Member',
    ]);

    // 2. Viewer update
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Original Name 2',
    ]);

    $response = $this->actingAs($viewer)
        ->putJson("/api/v1/workspaces/{$workspace2->id}/projects/{$project2->id}", [
            'name' => 'Updated by Viewer',
        ]);

    $response->assertStatus(403);
});

it('allows admin and member to delete project, but denies viewer', function () {
    // 1. Member delete
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project to Delete',
    ]);

    $response = $this->actingAs($member)
        ->deleteJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);

    // 2. Viewer delete
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Project to Delete 2',
    ]);

    $response = $this->actingAs($viewer)
        ->deleteJson("/api/v1/workspaces/{$workspace2->id}/projects/{$project2->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('projects', [
        'id' => $project2->id,
    ]);
});
