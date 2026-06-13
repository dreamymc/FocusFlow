<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Actions\CreateProjectAction;
use App\Actions\UpdateProjectAction;
use App\Actions\DeleteProjectAction;
use App\Http\Resources\ProjectResource;
use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(Workspace $workspace): AnonymousResourceCollection
    {
        $projects = Project::forWorkspace($workspace)->get();
        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request, Workspace $workspace, CreateProjectAction $createProjectAction): ProjectResource
    {
        if ($request->user()->cannot('create', Project::class)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $project = $createProjectAction->execute($workspace, $request->validated());
        return new ProjectResource($project);
    }

    public function show(Workspace $workspace, Project $project): ProjectResource
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        return new ProjectResource($project);
    }

    public function update(StoreProjectRequest $request, Workspace $workspace, Project $project, UpdateProjectAction $updateProjectAction): ProjectResource
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        if ($request->user()->cannot('update', $project)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $project = $updateProjectAction->execute($project, $request->validated());
        return new ProjectResource($project);
    }

    public function destroy(Request $request, Workspace $workspace, Project $project, DeleteProjectAction $deleteProjectAction): Response
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        if ($request->user()->cannot('delete', $project)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $deleteProjectAction->execute($project);

        return response()->noContent();
    }
}
