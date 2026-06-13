<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\Workspace;

class CreateProjectAction
{
    public function execute(Workspace $workspace, array $data): Project
    {
        return Project::create([
            'workspace_id' => $workspace->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
