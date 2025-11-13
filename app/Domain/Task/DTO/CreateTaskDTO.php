<?php

namespace App\Domain\Task\DTO;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use Carbon\Carbon;

readonly class CreateTaskDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?Carbon $dueDate,
        public int $userId,
        public ?int $assignedTo = null,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        $status = isset($data['status']) 
            ? TaskStatus::tryFrom($data['status']) ?? TaskStatus::PENDING
            : TaskStatus::PENDING;

        $priority = isset($data['priority'])
            ? TaskPriority::tryFrom($data['priority']) ?? TaskPriority::MEDIUM
            : TaskPriority::MEDIUM;

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $status,
            priority: $priority,
            dueDate: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            userId: $userId,
            assignedTo: $data['assigned_to'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'due_date' => $this->dueDate?->toDateTimeString(),
            'user_id' => $this->userId,
            'assigned_to' => $this->assignedTo,
        ];
    }
}
