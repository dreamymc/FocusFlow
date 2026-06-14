<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'workspace_id' => function (array $attributes) {
                return \App\Models\Project::find($attributes['project_id'])->workspace_id;
            },
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => \App\Enums\TaskStatus::Todo->value,
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
