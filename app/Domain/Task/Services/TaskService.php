<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function create(array $data, User $user): Task
    {
        return $user->tasks()->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->tasks()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
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
            'completed' => Task::where('status', 'completed')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'pending' => Task::where('status', 'pending')->count(),
            'overdue' => Task::where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];
    }
}
