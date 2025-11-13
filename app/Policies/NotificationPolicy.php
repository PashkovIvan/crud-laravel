<?php

namespace App\Policies;

use App\Domain\Notification\Models\Notification;
use App\Domain\User\Models\User;

class NotificationPolicy
{
    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}
