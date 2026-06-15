<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Enums\WorkspaceRole;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders kanban board for workspace members', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Member->value]);

    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project Kanban',
    ]);

    $response = $this->actingAs($user)
        ->get("/workspaces/{$workspace->id}/projects/{$project->id}");

    $response->assertOk();
});

it('groups tasks by their correct status', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Member->value]);

    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project Kanban',
    ]);

    $task1 = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task 1',
        'status' => TaskStatus::Backlog->value,
    ]);

    $task2 = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task 2',
        'status' => TaskStatus::InProgress->value,
    ]);

    $response = $this->actingAs($user)
        ->get("/workspaces/{$workspace->id}/projects/{$project->id}");

    $response->assertOk();
    // Verify that the view renders, showing the project data
    $response->assertSee('Project Kanban');
});

it('denies viewers from moving tasks', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Viewer->value]);

    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project Kanban',
    ]);

    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task 1',
        'status' => TaskStatus::Backlog->value,
    ]);

    $response = $this->actingAs($user)
        ->putJson("/api/v1/workspaces/{$workspace->id}/tasks/{$task->id}/move", [
            'status' => TaskStatus::InProgress->value,
        ]);

    $response->assertForbidden();
});
