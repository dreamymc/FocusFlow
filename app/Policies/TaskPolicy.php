<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Models\Workspace;

class TaskPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $user->workspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    public function view(User $user, Task $task): bool
    {
        return $user->workspaces()->where('workspaces.id', $task->workspace_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'member']);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasRole(['admin', 'member'])
            && $user->workspaces()->where('workspaces.id', $task->workspace_id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole(['admin', 'member'])
            && $user->workspaces()->where('workspaces.id', $task->workspace_id)->exists();
    }
}
