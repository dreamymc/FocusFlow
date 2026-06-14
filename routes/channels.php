<?php

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('workspace.{id}', function (User $user, $id) {
    return $user->workspaces->contains('id', $id);
});

Broadcast::channel('task.{id}', function (User $user, $id) {
    $task = Task::find($id);
    if (! $task) {
        return false;
    }

    if ($user->workspaces->contains('id', $task->workspace_id)) {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
    }

    return false;
});
