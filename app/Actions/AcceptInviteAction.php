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
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            abort(404, 'Invalid or expired invitation token.');
        }

        if ($invitation->email !== $user->email) {
            abort(403, 'This invitation was sent to a different email address.');
        }

        /** @var Workspace $workspace */
        $workspace = $invitation->workspace;

        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role->value]
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($workspace->id);
        
        $role = \Spatie\Permission\Models\Role::findOrCreate($invitation->role->value, 'web');
        $user->assignRole($role);

        $invitation->delete();

        return $workspace;
    }
}
