<?php

namespace App\Domain\Notification\DTO;

use App\Domain\Notification\Enums\NotificationType;

readonly class CreateNotificationDTO
{
    public function __construct(
        public string $title,
        public string $message,
        public NotificationType $type,
        public int $userId,
        public ?array $data = null,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        $type = isset($data['type'])
            ? NotificationType::tryFrom($data['type']) ?? NotificationType::INFO
            : NotificationType::INFO;

        return new self(
            title: $data['title'],
            message: $data['message'],
            type: $type,
            userId: $userId,
            data: $data['data'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type->value,
            'user_id' => $this->userId,
            'data' => $this->data,
        ];
    }
}
