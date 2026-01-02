<?php

namespace Tests\Feature;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $userData = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('register'), $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                    'token',
                    'motivation_message'
                ]
            ])
            ->assertJsonPath('data.motivation_message', fn($value) => is_string($value) && !empty($value));

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    public function test_user_can_login(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = 'password123';

        // problem: $user не используется дальше нигде
        $user = User::factory()->create([
            'email' => $email,
            'password' => $password,
        ]);

        $loginData = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson(route('login'), $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                    'motivation_message'
                ]
            ])
            ->assertJsonPath('data.motivation_message', fn($value) => is_string($value) && !empty($value));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('logout'));

        $response->assertStatus(200);
    }

    public function test_registration_validation(): void
    {
        $response = $this->postJson(route('register'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_validation(): void
    {
        $response = $this->postJson(route('login'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_invalid_credentials(): void
    {
        $email = fake()->unique()->safeEmail();

        $user = User::factory()->create([
            'email' => $email,
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson(route('login'), $loginData);

        $response->assertStatus(401);
    }

    public function test_user_can_get_me(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('me'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }
}
