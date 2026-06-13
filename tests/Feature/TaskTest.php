<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\Label;
use App\Enums\WorkspaceRole;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Testing\Fluent\AssertableJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows admin and member to create a task, but denies viewer', function () {
    // 1. Admin/Member test
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $assignee = User::factory()->create();
    $workspace->users()->attach($assignee, ['role' => 'member']);
    
    $label = Label::create([
        'workspace_id' => $workspace->id,
        'name' => 'Bug',
        'color' => '#ff0000',
    ]);

    $response = $this->actingAs($member)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks", [
            'title' => 'Fix database connection',
            'description' => 'We need to fix the sqlite driver issue',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'assignee_ids' => [$assignee->id],
            'label_ids' => [$label->id],
        ]);

    $response->assertCreated()
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('data.id')
                 ->where('data.title', 'Fix database connection')
                 ->where('data.status', TaskStatus::InProgress->value)
                 ->where('data.priority', TaskPriority::High->value)
                 ->etc()
        );

    $this->assertDatabaseHas('tasks', [
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Fix database connection',
    ]);

    $task = Task::where('title', 'Fix database connection')->first();
    $this->assertDatabaseHas('task_assignees', [
        'task_id' => $task->id,
        'user_id' => $assignee->id,
    ]);
    $this->assertDatabaseHas('task_label', [
        'task_id' => $task->id,
        'label_id' => $label->id,
    ]);

    // 2. Viewer test
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Project B',
    ]);

    $response = $this->actingAs($viewer)
        ->postJson("/api/v1/workspaces/{$workspace2->id}/projects/{$project2->id}/tasks", [
            'title' => 'Viewer Task',
        ]);

    $response->assertStatus(403);
});

it('validates task title is required', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);

    $response = $this->actingAs($member)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks", [
            'description' => 'Missing title',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('lists and filters tasks in a project', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);

    // Create 3 tasks with different status and assignees
    $task1 = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task One',
        'status' => TaskStatus::Backlog->value,
    ]);

    $task2 = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task Two',
        'status' => TaskStatus::InProgress->value,
    ]);
    $task2->assignees()->attach($member);

    $task3 = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task Three',
        'status' => TaskStatus::Done->value,
    ]);

    // 1. List all tasks (paginated)
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks");

    $response->assertOk()
        ->assertJsonCount(3, 'data');

    // 2. Filter by status
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks?status=in_progress");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Task Two');

    // 3. Filter by assignee 'me'
    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks?assignee=me");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Task Two');
});

it('shows a task to workspace members', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Target Task',
    ]);

    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}");

    $response->assertOk()
        ->assertJsonPath('data.title', 'Target Task');

    // Non-member access is denied
    $stranger = User::factory()->create();
    $response = $this->actingAs($stranger)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}");

    $response->assertStatus(403);
});

it('allows admin and member to update a task, but denies viewer', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Original Title',
    ]);

    $response = $this->actingAs($member)
        ->putJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}", [
            'title' => 'Updated Title',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
    ]);

    // Viewer update
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Project B',
    ]);
    $task2 = Task::create([
        'workspace_id' => $workspace2->id,
        'project_id' => $project2->id,
        'title' => 'Original Title 2',
    ]);

    $response = $this->actingAs($viewer)
        ->putJson("/api/v1/workspaces/{$workspace2->id}/projects/{$project2->id}/tasks/{$task2->id}", [
            'title' => 'Viewer Update',
        ]);

    $response->assertStatus(403);
});

it('allows admin and member to delete a task, but denies viewer', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task to Delete',
    ]);

    $response = $this->actingAs($member)
        ->deleteJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);

    // Viewer delete
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Project B',
    ]);
    $task2 = Task::create([
        'workspace_id' => $workspace2->id,
        'project_id' => $project2->id,
        'title' => 'Task to Delete 2',
    ]);

    $response = $this->actingAs($viewer)
        ->deleteJson("/api/v1/workspaces/{$workspace2->id}/projects/{$project2->id}/tasks/{$task2->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('tasks', [
        'id' => $task2->id,
    ]);
});

it('allows workspace member to move a task status, but denies viewer', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task to Move',
        'status' => TaskStatus::Backlog->value,
    ]);

    $response = $this->actingAs($member)
        ->putJson("/api/v1/workspaces/{$workspace->id}/tasks/{$task->id}/move", [
            'status' => TaskStatus::Done->value,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', TaskStatus::Done->value);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => TaskStatus::Done->value,
    ]);

    // Viewer move
    [$workspace2, $viewer] = createWorkspaceWithUser(WorkspaceRole::Viewer);
    $project2 = Project::create([
        'workspace_id' => $workspace2->id,
        'name' => 'Project B',
    ]);
    $task2 = Task::create([
        'workspace_id' => $workspace2->id,
        'project_id' => $project2->id,
        'title' => 'Task to Move 2',
        'status' => TaskStatus::Backlog->value,
    ]);

    $response = $this->actingAs($viewer)
        ->putJson("/api/v1/workspaces/{$workspace2->id}/tasks/{$task2->id}/move", [
            'status' => TaskStatus::Done->value,
        ]);

    $response->assertStatus(403);
});
