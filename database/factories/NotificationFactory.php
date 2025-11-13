<?php

namespace Database\Factories;

use App\Domain\Notification\Enums\NotificationType;
use App\Domain\Notification\Models\Notification;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(NotificationType::cases()),
            'read_at' => fake()->optional()->dateTime(),
            'user_id' => User::factory(),
            'data' => fake()->optional()->array(),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }
}
