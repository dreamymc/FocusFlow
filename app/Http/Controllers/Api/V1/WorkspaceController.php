<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Actions\CreateWorkspaceAction;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkspaceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return WorkspaceResource::collection($request->user()->workspaces);
    }

    public function store(StoreWorkspaceRequest $request, CreateWorkspaceAction $createWorkspaceAction): WorkspaceResource
    {
        $workspace = $createWorkspaceAction->execute(
            $request->validated('name'),
            $request->user()
        );

        return (new WorkspaceResource($workspace))->response()->setStatusCode(201);
    }

    public function show(Workspace $workspace): WorkspaceResource
    {
        if (!auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
            abort(403, 'You do not have access to this workspace.');
        }

        return new WorkspaceResource($workspace);
    }
}
