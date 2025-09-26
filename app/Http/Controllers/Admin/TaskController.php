<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAll($request->get('per_page', 15));
        
        return response()->json([
            'data' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated(), $request->user());
        
        return response()->json([
            'data' => new TaskResource($task)
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $task = $this->taskService->getById($task->id);
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->taskService->update($task, $request->validated());
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);
        
        return response()->json(null, 204);
    }

    public function markCompleted(Task $task): JsonResponse
    {
        $task = $this->taskService->markAsCompleted($task);
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    public function markInProgress(Task $task): JsonResponse
    {
        $task = $this->taskService->markAsInProgress($task);
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    public function markPending(Task $task): JsonResponse
    {
        $task = $this->taskService->markAsPending($task);
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    public function assign(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = \App\Domain\User\Models\User::findOrFail($request->user_id);
        $task = $this->taskService->assignToUser($task, $user);
        
        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }
}
