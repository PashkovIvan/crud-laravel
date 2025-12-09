<?php

namespace App\Domain\Notification\Models;

use App\Domain\Notification\Enums\NotificationType;
use App\Domain\User\Models\User;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'read_at',
        'user_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'read_at' => 'datetime',
            'data' => 'array',
        ];
    }

    protected static function newFactory()
    {
        return NotificationFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->read_at = now();
        $this->save();
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}
