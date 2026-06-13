<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Workspace;

class ProjectPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $user->workspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->workspaces()->where('workspaces.id', $project->workspace_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'member']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'member'])
            && $user->workspaces()->where('workspaces.id', $project->workspace_id)->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'member'])
            && $user->workspaces()->where('workspaces.id', $project->workspace_id)->exists();
    }
}
