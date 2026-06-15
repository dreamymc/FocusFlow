<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\Project;
use App\Actions\CreateProjectAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Workspace $workspace)
    {
        return Inertia::render('Projects/Index', [
            'workspace' => $workspace,
            'projects' => $workspace->projects()->withCount('tasks')->get()->map(function (\App\Models\Project $project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'tasks_count' => $project->tasks_count,
                ];
            }),
        ]);
    }

    public function store(Request $request, Workspace $workspace, CreateProjectAction $createProjectAction)
    {
        if ($request->user()->cannot('create', Project::class)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $createProjectAction->execute($workspace, $validated);

        return redirect()->route('workspaces.projects.index', $workspace)->with('success', 'Project created successfully!');
    }
}
