<?php

namespace App\Actions;

use App\Models\Task;
use App\Enums\TaskStatus;

class MoveTaskAction
{
    public function execute(Task $task, TaskStatus $status): Task
    {
        $previousStatus = $task->status;

        $task->update([
            'status' => $status->value,
        ]);

        if (class_exists(\App\Events\TaskMoved::class)) {
            event(new \App\Events\TaskMoved($task, $previousStatus));
        }

        return $task;
    }
}
