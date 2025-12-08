<?php

namespace App\Domain\Task\Services;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Task\DTO\CreateTaskDTO;
use App\Domain\Task\DTO\UpdateTaskDTO;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function create(CreateTaskDTO $dto): Task
    {
        $task = Task::create($dto->toArray());
        Cache::forget('task_statistics');

        return $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $task->update($dto->toArray());
        Cache::forget('task_statistics');

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        $result = $task->delete();
        Cache::forget('task_statistics');

        return $result;
    }

    public function getByUser(User $user, int $perPage = PaginationConstants::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $user->tasks()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(int $perPage = PaginationConstants::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return Task::with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getById(int $id): ?Task
    {
        return Task::with(['user', 'assignedUser'])->find($id);
    }

    public function markAsCompleted(Task $task): Task
    {
        $task->markAsCompleted();
        Cache::forget('task_statistics');

        return $task->fresh();
    }

    public function markAsInProgress(Task $task): Task
    {
        $task->markAsInProgress();
        Cache::forget('task_statistics');

        return $task->fresh();
    }

    public function markAsPending(Task $task): Task
    {
        $task->markAsPending();
        Cache::forget('task_statistics');

        return $task->fresh();
    }

    public function assignToUser(Task $task, User $user): Task
    {
        $task->update(['assigned_to' => $user->id]);
        Cache::forget('task_statistics');

        return $task->fresh();
    }

    public function getStatistics(): array
    {
        return Cache::remember('task_statistics', 300, fn() => [
            'total' => Task::count(),
            'completed' => Task::where('status', TaskStatus::COMPLETED)->count(),
            'in_progress' => Task::where('status', TaskStatus::IN_PROGRESS)->count(),
            'pending' => Task::where('status', TaskStatus::PENDING)->count(),
            'overdue' => Task::where('due_date', '<', now())
                ->whereNot('status', TaskStatus::COMPLETED)
                ->count(),
        ]);
    }
}
