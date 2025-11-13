<?php

namespace App\Http\Controllers\Api;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Common\Enums\ErrorMessage;
use App\Domain\Common\Enums\SuccessMessage;
use App\Domain\Notification\DTO\CreateNotificationDTO;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\ListNotificationRequest;
use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(ListNotificationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $perPage = $validated['per_page'] ?? PaginationConstants::DEFAULT_PER_PAGE;
            
            $notifications = $this->notificationService->getByUser($request->user(), $perPage);
            return $this->paginatedResponse($notifications, NotificationResource::class);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Notification listing');
        }
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        try {
            $dto = CreateNotificationDTO::fromArray($request->validated(), $request->user()->id);
            $notification = $this->notificationService->create($dto);
            
            return $this->successResponse(
                new NotificationResource($notification),
                SuccessMessage::NOTIFICATION_CREATED->value,
                201
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Notification creation');
        }
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        try {
            $this->authorize('update', $notification);
            
            $notification = $this->notificationService->markAsRead($notification);
            
            return $this->successResponse(
                new NotificationResource($notification),
                SuccessMessage::NOTIFICATION_READ->value
            );
        } catch (AuthorizationException $e) {
            return $this->errorResponse(ErrorMessage::FORBIDDEN->value, 403);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Notification mark as read');
        }
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $count = $this->notificationService->markAllAsRead($request->user());
            
            return $this->successResponse(
                ['count' => $count],
                SuccessMessage::NOTIFICATIONS_ALL_READ->value
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Mark all notifications as read');
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->notificationService->getUnreadCount($request->user());
            
            return $this->successResponse(['unread_count' => $count]);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Get unread count');
        }
    }
}
