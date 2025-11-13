<?php

namespace Tests\Unit;

use App\Domain\Notification\DTO\CreateNotificationDTO;
use App\Domain\Notification\Enums\NotificationType;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Services\NotificationService;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
        $this->user = User::factory()->create();
    }

    public function test_can_create_a_notification(): void
    {
        $data = [
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => NotificationType::INFO->value,
        ];

        $dto = CreateNotificationDTO::fromArray($data, $this->user->id);
        $notification = $this->notificationService->create($dto);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($this->user->id, $notification->user_id);

        $this->assertDatabaseHas('notifications', [
            'title' => $data['title'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_get_notifications_by_user(): void
    {
        Notification::factory()->count(3)->create();
        Notification::factory()->count(5)->create(['user_id' => $this->user->id]);

        $notifications = $this->notificationService->getByUser($this->user);

        $this->assertCount(5, $notifications->items());
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $updatedNotification = $this->notificationService->markAsRead($notification);

        $this->assertNotNull($updatedNotification->read_at);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => $updatedNotification->read_at,
        ]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $count = $this->notificationService->markAllAsRead($this->user);

        $this->assertEquals(3, $count);

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

        $count = $this->notificationService->getUnreadCount($this->user);

        $this->assertEquals(2, $count);
    }
}
