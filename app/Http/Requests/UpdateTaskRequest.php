<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => [
                'integer',
                Rule::exists('workspace_user', 'user_id')->where(function ($query) {
                    $workspace = $this->route('workspace');
                    $workspaceId = $workspace instanceof \App\Models\Workspace ? $workspace->id : $workspace;
                    return $query->where('workspace_id', $workspaceId);
                })
            ],
            'label_ids.*' => [
                'integer',
                Rule::exists('labels', 'id')->where(function ($query) {
                    $workspace = $this->route('workspace');
                    $workspaceId = $workspace instanceof \App\Models\Workspace ? $workspace->id : $workspace;
                    return $query->where('workspace_id', $workspaceId);
                })
            ],
        ];
    }
}
