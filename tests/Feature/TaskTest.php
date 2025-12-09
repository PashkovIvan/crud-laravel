<?php

namespace Tests\Feature;

use App\Domain\Common\Helpers\IdHelper;
use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
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
        $this->user = User::factory()->admin()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('admin.tasks.index'));

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
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'due_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('admin.tasks.store'), $taskData);

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
            'title' => $taskData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_show_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('admin.tasks.show', $task));

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
            'title' => fake()->sentence(),
            'status' => TaskStatus::IN_PROGRESS->value,
        ];

        $response = $this->putJson(route('admin.tasks.update', $task), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => $updateData['title'],
                    'status' => TaskStatus::IN_PROGRESS->value,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $updateData['title'],
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_can_delete_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson(route('admin.tasks.destroy', $task));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_can_mark_task_as_completed(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson(route('admin.tasks.completed', $task));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => TaskStatus::COMPLETED->value,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED->value,
        ]);
    }

    public function test_can_mark_task_as_in_progress(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson(route('admin.tasks.in-progress', $task));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => TaskStatus::IN_PROGRESS->value,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_can_mark_task_as_pending(): void
    {
        $task = Task::factory()->inProgress()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson(route('admin.tasks.pending', $task));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => TaskStatus::PENDING->value,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::PENDING->value,
        ]);
    }

    public function test_can_get_dashboard_statistics(): void
    {
        Task::factory()->count(5)->create(['user_id' => $this->user->id]);
        Task::factory()->completed()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('admin.dashboard.statistics'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tasks' => [
                        'total',
                        TaskStatus::COMPLETED->value,
                        TaskStatus::IN_PROGRESS->value,
                        TaskStatus::PENDING->value,
                        'overdue',
                    ],
                    'users' => [
                        'total',
                        'active',
                    ],
                    'recent_tasks'
                ]
            ]);
    }

    public function test_can_assign_task_to_user(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $assignedUser = User::factory()->create();

        $response = $this->patchJson(route('admin.tasks.assign', $task), [
            'user_id' => IdHelper::encrypt($assignedUser->id)
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'assigned_user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ])
            ->assertJsonPath('data.assigned_user.name', $assignedUser->name)
            ->assertJsonPath('data.assigned_user.email', $assignedUser->email);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }
}