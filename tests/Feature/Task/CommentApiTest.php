<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use App\Enums\WorkspaceRole;
use Illuminate\Testing\Fluent\AssertableJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows workspace member to retrieve task comments', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task One',
    ]);

    $comment = Comment::create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'content' => 'First test comment',
    ]);

    $response = $this->actingAs($member)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}/comments");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'First test comment')
        ->assertJsonPath('data.0.user.id', $member->id);
});

it('allows workspace member to create a task comment', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task One',
    ]);

    \Illuminate\Support\Facades\Event::fake([\App\Events\TaskCommented::class]);

    $response = $this->actingAs($member)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}/comments", [
            'content' => 'This is a new comment',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'This is a new comment')
        ->assertJsonPath('data.user.id', $member->id);

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $member->id,
        'content' => 'This is a new comment',
    ]);

    \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\TaskCommented::class, function ($event) use ($task) {
        return $event->task->id === $task->id && $event->comment->content === 'This is a new comment';
    });
});

it('denies non-workspace member from posting or viewing comments', function () {
    [$workspace, $member] = createWorkspaceWithUser(WorkspaceRole::Member);
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'name' => 'Project A',
    ]);
    $task = Task::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'title' => 'Task One',
    ]);

    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger)
        ->getJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}/comments");

    $response->assertStatus(403);

    $response = $this->actingAs($stranger)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects/{$project->id}/tasks/{$task->id}/comments", [
            'content' => 'Intruder comment',
        ]);

    $response->assertStatus(403);
});
