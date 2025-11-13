<?php

namespace App\Domain\Task\Models;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'user_id',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => TaskStatus::COMPLETED]);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => TaskStatus::IN_PROGRESS]);
    }

    public function markAsPending(): void
    {
        $this->update(['status' => TaskStatus::PENDING]);
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === TaskStatus::IN_PROGRESS;
    }

    public function isPending(): bool
    {
        return $this->status === TaskStatus::PENDING;
    }
}
