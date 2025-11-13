<?php

namespace App\Domain\Task\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum TaskStatus: string
{
    use HasEnumValues;

    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'В ожидании',
            self::IN_PROGRESS => 'В работе',
            self::COMPLETED => 'Завершено',
        };
    }
}
