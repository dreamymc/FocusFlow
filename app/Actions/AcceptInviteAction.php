<?php

namespace App\Actions;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Enums\InviteStatus;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Validation\ValidationException;

class AcceptInviteAction
{
    public function execute(string $token, User $user): Workspace
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', InviteStatus::Pending->value)
            ->first();

        if (!$invitation) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired invitation token.'],
            ]);
        }

        if ($invitation->email !== $user->email) {
            throw ValidationException::withMessages([
                'email' => ['This invitation was sent to a different email address.'],
            ]);
        }

        $workspace = $invitation->workspace;

        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role->value]
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($workspace->id);
        $user->assignRole($invitation->role->value);

        $invitation->update([
            'status' => InviteStatus::Accepted->value,
        ]);

        return $workspace;
    }
}
