<?php

namespace App\Actions;

use App\Models\Task;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $task->update([
            'title' => $data['title'] ?? $task->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $task->description,
            'status' => $data['status'] ?? $task->status->value,
            'priority' => $data['priority'] ?? $task->priority->value,
        ]);

        if (isset($data['assignee_ids'])) {
            $task->assignees()->sync($data['assignee_ids']);
        }

        if (isset($data['label_ids'])) {
            $task->labels()->sync($data['label_ids']);
        }

        return $task;
    }
}
