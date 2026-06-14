<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create two example users
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $member = User::factory()->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
        ]);

        // Create a workspace and attach users with roles
        $workspace = Workspace::factory()->create([
            'name' => 'Acme Corp',
        ]);
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);

        // Create two projects inside the workspace
        $projectA = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Marketing Campaign',
        ]);
        $projectB = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Product Redesign',
        ]);

        // Helper to create tasks with varying status/priority
        $statuses = ['backlog', 'in_progress', 'in_review', 'done'];
        $priorities = ['low', 'medium', 'high'];

        foreach ([$projectA, $projectB] as $project) {
            for ($i = 1; $i <= 5; $i++) {
                $task = Task::create([
                    'workspace_id' => $workspace->id,
                    'project_id' => $project->id,
                    'title' => "Task {$i} for {$project->name}",
                    'description' => "Auto‑generated description for task {$i}.",
                    'status' => $statuses[array_rand($statuses)],
                    'priority' => $priorities[array_rand($priorities)],
                ]);

                $assignee = ($i % 2 === 0) ? $member : $admin;
                $task->assignees()->attach($assignee->id);
            }
        }
    }
}
