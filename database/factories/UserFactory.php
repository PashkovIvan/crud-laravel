<?php

namespace Database\Factories;

use App\Domain\User\Enums\UserRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }

    public function manager(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => UserRole::MANAGER->value, 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }

    public function user(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => UserRole::USER->value, 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }
}
