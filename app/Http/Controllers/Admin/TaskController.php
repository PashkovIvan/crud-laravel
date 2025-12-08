<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Common\Enums\ErrorMessage;
use App\Domain\Common\Enums\SuccessMessage;
use App\Domain\Common\Helpers\IdHelper;
use App\Domain\Task\DTO\CreateTaskDTO;
use App\Domain\Task\DTO\UpdateTaskDTO;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Services\TaskService;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\ListTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(ListTaskRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $perPage = $validated['per_page'] ?? PaginationConstants::DEFAULT_PER_PAGE;
            
            $tasks = $this->taskService->getAll($perPage);

            return $this->paginatedResponse($tasks, TaskResource::class);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task listing');
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $dto = CreateTaskDTO::fromArray($request->validated(), $request->user()->id);
            $task = $this->taskService->create($dto);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_CREATED->value,
                201
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task creation');
        }
    }

    public function show(Task $task): JsonResponse
    {
        try {
            $task->load(['user', 'assignedUser']);

            return $this->successResponse(new TaskResource($task));
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task retrieval');
        }
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $dto = UpdateTaskDTO::fromArray($request->validated());
            $task = $this->taskService->update($task, $dto);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_UPDATED->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task update');
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            $this->taskService->delete($task);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task deletion');
        }
    }

    public function markCompleted(Task $task): JsonResponse
    {
        try {
            $task = $this->taskService->markAsCompleted($task);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_COMPLETED->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task completion');
        }
    }

    public function markInProgress(Task $task): JsonResponse
    {
        try {
            $task = $this->taskService->markAsInProgress($task);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_IN_PROGRESS->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task status update');
        }
    }

    public function markPending(Task $task): JsonResponse
    {
        try {
            $task = $this->taskService->markAsPending($task);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_PENDING->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task status update');
        }
    }

    public function assign(AssignTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $userId = IdHelper::decrypt($validated['user_id']);
            $user = User::findOrFail($userId);
            $task = $this->taskService->assignToUser($task, $user);

            return $this->successResponse(
                new TaskResource($task),
                SuccessMessage::TASK_ASSIGNED->value
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(ErrorMessage::USER_NOT_FOUND->value, 404);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Task assignment');
        }
    }
}
