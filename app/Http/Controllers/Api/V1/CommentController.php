<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use App\Events\TaskCommented;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function index(Workspace $workspace, Project $project, Task $task)
    {
        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        return response()->json([
            'data' => $task->comments()->with('user')->get()
        ]);
    }

    public function store(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        try {
            event(new TaskCommented($task, $comment));
        } catch (\Illuminate\Broadcasting\BroadcastException $e) {
            report($e);
        }

        return response()->json([
            'data' => $comment->load('user')
        ], Response::HTTP_CREATED);
    }
}
