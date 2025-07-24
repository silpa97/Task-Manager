<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $teamLead = User::factory()->create(['role' => 'team_lead']);
        $developer = User::factory()->create(['role' => 'developer']);
        $project = Project::factory()->create(['assigned_to' => $teamLead->id]);

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'project_id' => $project->id,
            'assigned_to' => $developer->id,
            'created_by' => $teamLead->id,
            'due_time' => now()->addDays(3),
            'status' => 'pending',
        ];
    }
}
