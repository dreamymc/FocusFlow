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

    public function store(StoreWorkspaceRequest $request, CreateWorkspaceAction $createWorkspaceAction): \Illuminate\Http\JsonResponse
    {
        $workspace = $createWorkspaceAction->execute(
            $request->validated('name'),
            $request->user()
        );

        return (new WorkspaceResource($workspace))->response()->setStatusCode(201);
    }

    public function show(\App\Http\Requests\ShowWorkspaceRequest $request, Workspace $workspace): WorkspaceResource
    {
        return new WorkspaceResource($workspace);
    }

    public function billingPortal(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            abort(403, 'Only workspace admins can access the billing portal.');
        }

        return response()->json([
            'data' => [
                'url' => $workspace->billingPortalUrl(url('/'))
            ]
        ]);
    }
}
