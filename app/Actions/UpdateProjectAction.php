<?php

namespace App\Actions;

use App\Models\Project;

class UpdateProjectAction
{
    public function execute(Project $project, array $data): Project
    {
        $project->update([
            'name' => $data['name'] ?? $project->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $project->description,
        ]);

        return $project;
    }
}
