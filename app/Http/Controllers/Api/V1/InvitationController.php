<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Actions\InviteMemberAction;
use App\Actions\AcceptInviteAction;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use App\Http\Resources\WorkspaceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvitationController extends Controller
{
    public function invite(StoreInvitationRequest $request, Workspace $workspace, InviteMemberAction $inviteMemberAction): JsonResponse
    {

        $invitation = $inviteMemberAction->execute(
            $workspace,
            $request->validated('email'),
            WorkspaceRole::from($request->validated('role'))
        );

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'token' => $invitation->token,
            ]
        ], 201);
    }

    public function accept(\App\Http\Requests\AcceptInvitationRequest $request, AcceptInviteAction $acceptInviteAction): JsonResponse
    {
        $workspace = $acceptInviteAction->execute(
            $request->input('token'),
            $request->user()
        );

        return response()->json([
            'message' => 'Joined workspace successfully.',
            'data' => new WorkspaceResource($workspace)
        ], 200);
    }
}
