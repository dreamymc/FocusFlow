<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateWorkspaceAction
{
    public function execute(string $name, User $user): Workspace
    {
        $workspace = Workspace::create([
            'name' => $name,
        ]);

        $workspace->users()->attach($user->id, [
            'role' => WorkspaceRole::Admin->value,
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($workspace->id);

        Role::findOrCreate(WorkspaceRole::Admin->value);
        Role::findOrCreate(WorkspaceRole::Member->value);
        Role::findOrCreate(WorkspaceRole::Viewer->value);

        $user->assignRole(WorkspaceRole::Admin->value);

        return $workspace;
    }
}
