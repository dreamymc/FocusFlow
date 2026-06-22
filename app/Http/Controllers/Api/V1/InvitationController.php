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
            WorkspaceRole::from($request->validated('role')),
            $request->user()
        );

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
            ]
        ], 201);
    }

    public function pending(Request $request): JsonResponse
    {
        $invitations = \App\Models\Invitation::where('email', $request->user()->email)
            ->where('status', \App\Enums\InviteStatus::Pending)
            ->with('workspace')
            ->get();

        return response()->json([
            'data' => \App\Http\Resources\InvitationResource::collection($invitations),
        ]);
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

    public function acceptById(\App\Models\Invitation $invitation, Request $request, AcceptInviteAction $acceptInviteAction): JsonResponse
    {
        if ($invitation->email !== $request->user()->email) {
            abort(403);
        }

        $acceptInviteAction->execute($invitation->token, $request->user());

        return response()->json([
            'message' => 'Joined workspace successfully.'
        ], 200);
    }

    public function declineById(\App\Models\Invitation $invitation, Request $request): JsonResponse
    {
        if ($invitation->email !== $request->user()->email) {
            abort(403);
        }

        $invitation->update(['status' => \App\Enums\InviteStatus::Declined]);

        return response()->json([
            'message' => 'Invitation declined.'
        ], 200);
    }
}
