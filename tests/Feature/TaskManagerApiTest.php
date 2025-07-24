<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(User $user)
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('api-token')->plainTextToken];
    }

    public function test_user_registration_and_login()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['user' => ['id', 'name', 'email']]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    public function test_admin_can_assign_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authenticate($admin))
            ->postJson('/api/assign-role', [
                'user_id' => $user->id,
                'role' => 'project_manager'
            ]);

        $response->assertStatus(200)->assertJsonPath('user.role', 'project_manager');
    }

    public function test_project_manager_can_create_project()
    {
        $pm = User::factory()->create(['role' => 'project_manager']);
        $teamLead = User::factory()->create(['role' => 'team_lead']);

        $response = $this->withHeaders($this->authenticate($pm))
            ->postJson('/api/projects', [
                'title' => 'Sample Project',
                'description' => 'Test project',
                'assigned_to' => $teamLead->id,
                'end_date' => now()->addDays(7)->toDateTimeString()
            ]);

        $response->assertStatus(201)->assertJsonPath('project.title', 'Sample Project');
    }

    public function test_team_lead_can_assign_task_to_developer()
    {
        $teamLead = User::factory()->create(['role' => 'team_lead']);
        $developer = User::factory()->create(['role' => 'developer']);

        $project = Project::factory()->create([
            'assigned_to' => $teamLead->id,
        ]);

        $response = $this->withHeaders($this->authenticate($teamLead))
            ->postJson('/api/tasks', [
                'title' => 'New Task',
                'description' => 'Task desc',
                'project_id' => $project->id,
                'assigned_to' => $developer->id,
                'due_time' => now()->addDays(3)->toDateTimeString(),
            ]);

        $response->assertStatus(201)->assertJsonPath('task.title', 'New Task');
    }

    public function test_developer_can_update_task_status()
    {
        $teamLead = User::factory()->create(['role' => 'team_lead']);
        $developer = User::factory()->create(['role' => 'developer']);
        $project = Project::factory()->create(['assigned_to' => $teamLead->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $developer->id,
            'created_by' => $teamLead->id,
            'status' => 'pending'
        ]);

        $response = $this->withHeaders($this->authenticate($developer))
            ->putJson('/api/tasks/' . $task->id, [
                'status' => 'in_progress'
            ]);

        $response->assertStatus(200)->assertJsonPath('task.status', 'in_progress');
    }
}
