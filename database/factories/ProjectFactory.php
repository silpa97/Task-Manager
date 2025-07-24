<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $teamLead = User::factory()->create(['role' => 'team_lead']);
        $projectManager = User::factory()->create(['role' => 'project_manager']);

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'assigned_to' => $teamLead->id,         // Team lead
            'created_by' => $projectManager->id,     // Project manager
            'end_date' => now()->addDays(7),
        ];
    }
}
