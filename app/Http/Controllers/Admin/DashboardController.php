<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Task\Services\TaskService;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function statistics(): JsonResponse
    {
        $taskStats = $this->taskService->getStatistics();
        
        $userStats = [
            'total' => User::count(),
            'active' => User::whereHas('tasks')->count(),
        ];

        return response()->json([
            'tasks' => $taskStats,
            'users' => $userStats,
            'recent_tasks' => $this->taskService->getAll(5)->items(),
        ]);
    }
}
