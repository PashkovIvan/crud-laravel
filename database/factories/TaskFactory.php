<?php

namespace Database\Factories;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'user_id' => User::factory(),
            'assigned_to' => fake()->optional()->randomElement(User::pluck('id')),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::PENDING,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::COMPLETED,
        ]);
    }
}
