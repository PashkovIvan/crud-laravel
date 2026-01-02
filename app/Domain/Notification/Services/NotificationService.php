<?php

namespace App\Domain\Notification\Services;

use App\Domain\Common\Constants\PaginationConstants;
use App\Domain\Notification\DTO\CreateNotificationDTO;
use App\Domain\Notification\Models\Notification;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function create(CreateNotificationDTO $dto): Notification
    {
        return Notification::create($dto->toArray());
    }

    public function getByUser(User $user, int $perPage = PaginationConstants::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();

        // problem: лишний запрос?
        return $notification->fresh();
    }

    public function markAllAsRead(User $user): int
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        //problem: mb cache?
        return $user->notifications()
            ->whereNull('read_at')
            ->count();
    }
}
