<?php

namespace Tests\Support;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Attach a user to a workspace with the given role in BOTH:
 *   - the workspace_user pivot table  (for WorkspaceScope membership checks)
 *   - the Spatie model_has_roles table (for hasRole() checks in controllers)
 *
 * Resets the global PermissionRegistrar team-ID singleton to null after
 * assignment to prevent state leaking into subsequent tests.
 */
function attachWithRole(Workspace $workspace, User $user, WorkspaceRole $role): void
{
    $workspace->users()->attach($user->id, ['role' => $role->value]);

    $registrar = app(PermissionRegistrar::class);
    $registrar->setPermissionsTeamId($workspace->id);

    Role::findOrCreate($role->value, 'web');
    $user->assignRole($role->value);

    // Reset team-ID so it does not bleed into subsequent tests
    $registrar->setPermissionsTeamId(null);
}
