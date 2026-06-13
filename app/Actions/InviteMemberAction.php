<?php

namespace App\Actions;

use App\Models\Invitation;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use Illuminate\Support\Str;

class InviteMemberAction
{
    public function execute(Workspace $workspace, string $email, WorkspaceRole $role): Invitation
    {
        Invitation::where('workspace_id', $workspace->id)
            ->where('email', $email)
            ->where('status', InviteStatus::Pending->value)
            ->delete();

        return Invitation::create([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'role' => $role->value,
            'token' => Str::random(40),
            'status' => InviteStatus::Pending->value,
        ]);
    }
}
