<?php

namespace App\Actions;

use App\Models\Task;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $previousAssigneeId = $task->assignees->first()?->id;

        $task->update([
            'title' => $data['title'] ?? $task->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $task->description,
            'status' => $data['status'] ?? $task->status->value,
            'priority' => $data['priority'] ?? $task->priority->value,
        ]);

        if (isset($data['assignee_ids'])) {
            $task->assignees()->sync($data['assignee_ids']);

            $newAssigneeId = count($data['assignee_ids']) > 0 ? (int)$data['assignee_ids'][0] : null;
            if ($newAssigneeId && $newAssigneeId !== $previousAssigneeId) {
                $assignee = \App\Models\User::find($newAssigneeId);
                if ($assignee) {
                    try {
                        event(new \App\Events\TaskAssigned($task, $assignee));
                    } catch (\Illuminate\Broadcasting\BroadcastException $e) {
                        report($e);
                    }
                }
            }
        }

        if (isset($data['label_ids'])) {
            $task->labels()->sync($data['label_ids']);
        }

        try {
            event(new \App\Events\TaskUpdated($task));
        } catch (\Illuminate\Broadcasting\BroadcastException $e) {
            report($e);
        }

        return $task;
    }
}
