<?php

namespace App\Providers;

use App\Domain\Common\Helpers\IdHelper;
use App\Domain\Notification\Models\Notification;
use App\Domain\Task\Models\Task;
use App\Policies\NotificationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Notification::class => NotificationPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('task', function (string $value) {
            try {
                $decryptedId = IdHelper::decrypt($value);
                return Task::findOrFail($decryptedId);
            } catch (InvalidArgumentException $e) {
                return Task::findOrFail($value);
            }
        });

        Route::bind('notification', function (string $value) {
            try {
                $decryptedId = IdHelper::decrypt($value);
                return Notification::findOrFail($decryptedId);
            } catch (InvalidArgumentException $e) {
                return Notification::findOrFail($value);
            }
        });
    }
}
