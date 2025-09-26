<?php

namespace Tests\Feature;

use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/admin/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'due_date',
                        'user',
                        'assigned_user',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'pagination'
            ]);
    }

    public function test_can_create_a_task(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'due_date' => now()->addDay()->toISOString(),
        ];

        $response = $this->postJson('/api/admin/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'user',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_show_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/admin/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'user',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    public function test_can_update_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ];

        $response = $this->putJson("/api/admin/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Task',
                    'status' => 'in_progress',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ]);
    }

    public function test_can_delete_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/admin/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_can_mark_task_as_completed(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson("/api/admin/tasks/{$task->id}/completed");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'completed',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_get_dashboard_statistics(): void
    {
        Task::factory()->count(5)->create(['user_id' => $this->user->id]);
        Task::factory()->completed()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/admin/dashboard/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    'total',
                    'completed',
                    'in_progress',
                    'pending',
                    'overdue',
                ],
                'users' => [
                    'total',
                    'active',
                ],
                'recent_tasks'
            ]);
    }

    public function test_can_assign_task_to_user(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $assignedUser = User::factory()->create();

        $response = $this->patchJson("/api/admin/tasks/{$task->id}/assign", [
            'user_id' => $assignedUser->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'assigned_user' => [
                        'id' => $assignedUser->id,
                        'name' => $assignedUser->name,
                        'email' => $assignedUser->email,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }
}