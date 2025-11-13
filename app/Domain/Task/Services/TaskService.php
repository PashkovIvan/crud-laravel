<?php

namespace App\Domain\Task\Services;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Task\DTO\CreateTaskDTO;
use App\Domain\Task\DTO\UpdateTaskDTO;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function create(CreateTaskDTO $dto): Task
    {
        return Task::create($dto->toArray());
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $task->update($dto->toArray());
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
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
        return $task->fresh();
    }

    public function markAsInProgress(Task $task): Task
    {
        $task->markAsInProgress();
        return $task->fresh();
    }

    public function markAsPending(Task $task): Task
    {
        $task->markAsPending();
        return $task->fresh();
    }

    public function assignToUser(Task $task, User $user): Task
    {
        $task->update(['assigned_to' => $user->id]);
        return $task->fresh();
    }

    public function getStatistics(): array
    {
        return [
            'total' => Task::count(),
            'completed' => Task::where('status', TaskStatus::COMPLETED)->count(),
            'in_progress' => Task::where('status', TaskStatus::IN_PROGRESS)->count(),
            'pending' => Task::where('status', TaskStatus::PENDING)->count(),
            'overdue' => Task::where('due_date', '<', now())
                ->whereNot('status', TaskStatus::COMPLETED)
                ->count(),
        ];
    }
}
