<?php

namespace App\Actions;

use App\Models\Invitation;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\WorkspaceInvitation;

class InviteMemberAction
{
    public function execute(Workspace $workspace, string $email, WorkspaceRole $role, User $inviter): Invitation
    {
        // Delete ANY existing invitation for this workspace+email
        // (not just Pending — Declined records would violate unique constraint)
        Invitation::where('workspace_id', $workspace->id)
            ->where('email', $email)
            ->delete();

        $invitation = Invitation::create([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'role' => $role->value,
            'token' => Str::random(40),
            'status' => InviteStatus::Pending->value,
        ]);

        // Send notification
        $invitedUser = User::where('email', $email)->first();

        if ($invitedUser) {
            // Registered user → mail + database + broadcast
            $invitedUser->notify(new WorkspaceInvitation($invitation, $inviter->name));
        } else {
            // Unregistered email → mail-only via on-demand notification
            $notifiable = new \Illuminate\Notifications\AnonymousNotifiable();
            $notifiable->route('mail', $email);
            $notifiable->notify(new WorkspaceInvitation($invitation, $inviter->name));
        }

        return $invitation;
    }
}
