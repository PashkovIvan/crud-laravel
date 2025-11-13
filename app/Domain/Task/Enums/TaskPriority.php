<?php

namespace App\Domain\Task\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum TaskPriority: string
{
    use HasEnumValues;

    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Низкий',
            self::MEDIUM => 'Средний',
            self::HIGH => 'Высокий',
        };
    }
}
