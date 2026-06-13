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
        $pivot = $request->user()->workspaces()->where('workspaces.id', $workspace->id)->first()?->pivot;
        if (!$pivot || $pivot->role !== WorkspaceRole::Admin->value) {
            abort(403, 'Only workspace admins can invite new members.');
        }

        $invitation = $inviteMemberAction->execute(
            $workspace,
            $request->validated('email'),
            WorkspaceRole::from($request->validated('role'))
        );

        return response()->json([
            'message' => 'Invite sent successfully',
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'token' => $invitation->token,
            ]
        ], 201);
    }

    public function accept(Request $request, string $token, AcceptInviteAction $acceptInviteAction): JsonResponse
    {
        $workspace = $acceptInviteAction->execute(
            $token,
            $request->user()
        );

        return response()->json([
            'message' => 'Invite accepted',
            'data' => new WorkspaceResource($workspace)
        ], 200);
    }
}
