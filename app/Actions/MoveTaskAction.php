<?php

namespace App\Actions;

use App\Models\Task;
use App\Enums\TaskStatus;
use App\Events\TaskMoved;
use App\Events\TaskCompleted;

class MoveTaskAction
{
    public function execute(Task $task, TaskStatus $status): Task
    {
        $previousStatus = $task->status;

        $task->update([
            'status' => $status->value,
        ]);

        event(new TaskMoved($task, $previousStatus));

        if ($status === TaskStatus::Done && $previousStatus !== TaskStatus::Done) {
            event(new TaskCompleted($task));
        }

        return $task;
    }
}
