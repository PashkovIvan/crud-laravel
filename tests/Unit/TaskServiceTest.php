<?php

namespace Tests\Unit;

use App\Domain\Task\DTO\CreateTaskDTO;
use App\Domain\Task\DTO\UpdateTaskDTO;
use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Services\TaskService;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = new TaskService();
        $this->user = User::factory()->create();
    }

    public function test_can_create_a_task(): void
    {
        $data = [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => TaskPriority::HIGH->value,
        ];

        $dto = CreateTaskDTO::fromArray($data, $this->user->id);
        $task = $this->taskService->create($dto);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($data['title'], $task->title);
        $this->assertEquals($this->user->id, $task->user_id);

        $this->assertDatabaseHas('tasks', [
            'title' => $data['title'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_update_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'title' => fake()->sentence(),
            'status' => TaskStatus::IN_PROGRESS->value,
        ];

        $dto = UpdateTaskDTO::fromArray($data);
        $updatedTask = $this->taskService->update($task, $dto);

        $this->assertEquals($data['title'], $updatedTask->title);
        $this->assertEquals(TaskStatus::IN_PROGRESS, $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $data['title'],
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_can_delete_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $result = $this->taskService->delete($task);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_can_get_tasks_by_user(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $tasks = $this->taskService->getByUser($this->user);

        $this->assertCount(3, $tasks->items());
    }

    public function test_can_get_all_tasks(): void
    {
        Task::factory()->count(5)->create();

        $tasks = $this->taskService->getAll();

        $this->assertCount(5, $tasks->items());
    }

    public function test_can_get_task_by_id(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $foundTask = $this->taskService->getById($task->id);

        $this->assertEquals($task->id, $foundTask->id);
    }

    public function test_can_mark_task_as_completed(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $updatedTask = $this->taskService->markAsCompleted($task);

        $this->assertEquals(TaskStatus::COMPLETED, $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED->value,
        ]);
    }

    public function test_can_mark_task_as_in_progress(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $updatedTask = $this->taskService->markAsInProgress($task);

        $this->assertEquals(TaskStatus::IN_PROGRESS, $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_can_mark_task_as_pending(): void
    {
        $task = Task::factory()->inProgress()->create(['user_id' => $this->user->id]);

        $updatedTask = $this->taskService->markAsPending($task);

        $this->assertEquals(TaskStatus::PENDING, $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::PENDING->value,
        ]);
    }

    public function test_can_assign_task_to_user(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $assignedUser = User::factory()->create();

        $updatedTask = $this->taskService->assignToUser($task, $assignedUser);

        $this->assertEquals($assignedUser->id, $updatedTask->assigned_to);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }

    public function test_can_get_statistics(): void
    {
        Task::factory()->count(5)->create();
        Task::factory()->completed()->count(3)->create();
        Task::factory()->inProgress()->count(2)->create();

        $stats = $this->taskService->getStatistics();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('completed', $stats);
        $this->assertArrayHasKey('in_progress', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('overdue', $stats);

        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(3, $stats['completed']);
        $this->assertEquals(2, $stats['in_progress']);
        $this->assertEquals(5, $stats['pending']);
    }
}
