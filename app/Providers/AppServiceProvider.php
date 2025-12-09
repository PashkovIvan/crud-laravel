<?php

namespace App\Providers;

use App\Domain\Common\Helpers\IdHelper;
use App\Domain\Motivation\Contracts\MotivationProviderInterface;
use App\Domain\Motivation\Providers\OllamaMotivationProvider;
use App\Domain\Notification\Models\Notification;
use App\Domain\Task\Models\Task;
use App\Policies\NotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MotivationProviderInterface::class, function ($app) {
            return new OllamaMotivationProvider();
        });
    }

    public function boot(): void
    {
        // Регистрация политик
        Gate::policy(Notification::class, NotificationPolicy::class);

        // Route model bindings
        Route::bind('task', fn(string $value) => $this->resolveTask($value));

        Route::bind('notification', fn(string $value) => $this->resolveNotification($value));
    }

    private function resolveTask(string $value): Task
    {
        try {
            $decryptedId = IdHelper::decrypt($value);

            return Task::findOrFail($decryptedId);
        } catch (InvalidArgumentException $e) {
            return Task::findOrFail($value);
        }
    }

    private function resolveNotification(string $value): Notification
    {
        try {
            $decryptedId = IdHelper::decrypt($value);

            return Notification::findOrFail($decryptedId);
        } catch (InvalidArgumentException $e) {
            return Notification::findOrFail($value);
        }
    }
}
