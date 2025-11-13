<?php

namespace App\Domain\Task\DTO;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use Carbon\Carbon;

readonly class UpdateTaskDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?TaskStatus $status = null,
        public ?TaskPriority $priority = null,
        public ?Carbon $dueDate = null,
        public ?int $assignedTo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) 
                ? TaskStatus::tryFrom($data['status']) 
                : null,
            priority: isset($data['priority']) 
                ? TaskPriority::tryFrom($data['priority']) 
                : null,
            dueDate: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            assignedTo: $data['assigned_to'] ?? null,
        );
    }

    public function toArray(): array
    {
        $array = [];

        if ($this->title !== null) {
            $array['title'] = $this->title;
        }
        if ($this->description !== null) {
            $array['description'] = $this->description;
        }
        if ($this->status !== null) {
            $array['status'] = $this->status->value;
        }
        if ($this->priority !== null) {
            $array['priority'] = $this->priority->value;
        }
        if ($this->dueDate !== null) {
            $array['due_date'] = $this->dueDate->toDateTimeString();
        }
        if ($this->assignedTo !== null) {
            $array['assigned_to'] = $this->assignedTo;
        }

        return $array;
    }
}
