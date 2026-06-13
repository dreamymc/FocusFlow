<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Actions\CreateTaskAction;
use App\Actions\UpdateTaskAction;
use App\Actions\MoveTaskAction;
use App\Actions\DeleteTaskAction;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCollection;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(Request $request, Workspace $workspace, Project $project): TaskCollection
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->forWorkspace($workspace)
            ->when($request->query('status'), fn($q, $s) => $q->byStatus($s))
            ->when($request->query('assignee'), function ($q, $a) use ($request) {
                $id = $a === 'me' ? $request->user()->id : $a;
                $q->assignedTo($id);
            })
            ->when($request->query('sort'), fn($q, $s) => $q->orderBy($s))
            ->with(['assignees', 'labels'])
            ->paginate(15);

        return new TaskCollection($tasks);
    }

    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project, CreateTaskAction $createTaskAction): TaskResource
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        if ($request->user()->cannot('create', Task::class)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $task = $createTaskAction->execute($project, $request->validated());
        return new TaskResource($task->load(['assignees', 'labels']));
    }

    public function show(Workspace $workspace, Project $project, Task $task): TaskResource
    {
        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        return new TaskResource($task->load(['assignees', 'labels']));
    }

    public function update(UpdateTaskRequest $request, Workspace $workspace, Project $project, Task $task, UpdateTaskAction $updateTaskAction): TaskResource
    {
        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        if ($request->user()->cannot('update', $task)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $task = $updateTaskAction->execute($task, $request->validated());
        return new TaskResource($task->load(['assignees', 'labels']));
    }

    public function destroy(Request $request, Workspace $workspace, Project $project, Task $task, DeleteTaskAction $deleteTaskAction): Response
    {
        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        if ($request->user()->cannot('delete', $task)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $deleteTaskAction->execute($task);

        return response()->noContent();
    }

    public function move(Request $request, Workspace $workspace, Task $task, MoveTaskAction $moveTaskAction): TaskResource
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        if ($request->user()->cannot('update', $task)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $request->validate([
            'status' => ['required', new \Illuminate\Validation\Rules\Enum(TaskStatus::class)],
        ]);

        $task = $moveTaskAction->execute($task, TaskStatus::from($request->input('status')));

        return new TaskResource($task->load(['assignees', 'labels']));
    }
}
