<?php

namespace Tests\Unit;

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
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
        ];

        $task = $this->taskService->create($data, $this->user);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals($this->user->id, $task->user_id);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_update_a_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ];

        $updatedTask = $this->taskService->update($task, $data);

        $this->assertEquals('Updated Task', $updatedTask->title);
        $this->assertEquals('in_progress', $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'in_progress',
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

        $this->assertCount(3, $tasks);
    }

    public function test_can_get_all_tasks(): void
    {
        Task::factory()->count(5)->create();

        $tasks = $this->taskService->getAll();

        $this->assertCount(5, $tasks);
    }

    public function test_can_mark_task_as_completed(): void
    {
        $task = Task::factory()->pending()->create(['user_id' => $this->user->id]);

        $updatedTask = $this->taskService->markAsCompleted($task);

        $this->assertEquals('completed', $updatedTask->status);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
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
    }
}
