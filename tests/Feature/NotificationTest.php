<?php

namespace Tests\Feature;

use App\Domain\Notification\Enums\NotificationType;
use App\Domain\Notification\Models\Notification;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_notifications(): void
    {
        Notification::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('notifications.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'type',
                        'read_at',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'pagination'
            ]);
    }

    public function test_can_create_notification(): void
    {
        $notificationData = [
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(NotificationType::cases())->value,
        ];

        $response = $this->postJson(route('notifications.store'), $notificationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'message',
                    'type',
                    'read_at',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('notifications', [
            'title' => $notificationData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $encryptedId = \App\Domain\Common\Helpers\IdHelper::encrypt($notification->id);
        $response = $this->patchJson(route('notifications.read', $encryptedId));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'read_at',
                ]
            ])
            ->assertJsonPath('data.read_at', fn($value) => !is_null($value));

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => $notification->fresh()->read_at,
        ]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->patchJson(route('notifications.mark-all-read'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Все уведомления отмечены как прочитанные',
                'data' => [
                    'count' => 3
                ]
            ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);
    }

    public function test_can_get_unread_count(): void
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => now(),
        ]);

        $response = $this->getJson(route('notifications.unread-count'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'unread_count' => 2
                ]
            ]);
    }
}
