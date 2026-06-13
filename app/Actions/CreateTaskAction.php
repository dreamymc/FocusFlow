<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\Task;

class CreateTaskAction
{
    public function execute(Project $project, array $data): Task
    {
        $task = Task::create([
            'project_id' => $project->id,
            'workspace_id' => $project->workspace_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? \App\Enums\TaskStatus::Backlog->value,
            'priority' => $data['priority'] ?? \App\Enums\TaskPriority::Medium->value,
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
