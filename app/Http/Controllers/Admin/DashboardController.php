<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Task\Services\TaskService;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function statistics(): JsonResponse
    {
        try {
            $taskStats = $this->taskService->getStatistics();
            
            $userStats = [
                'total' => User::count(),
                'active' => User::whereHas('tasks')->count(),
            ];

            $recentTasks = $this->taskService->getAll(PaginationConstants::RECENT_TASKS_LIMIT);

            return $this->successResponse([
                'tasks' => $taskStats,
                'users' => $userStats,
                'recent_tasks' => TaskResource::collection($recentTasks->items()),
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Dashboard statistics');
        }
    }
}
