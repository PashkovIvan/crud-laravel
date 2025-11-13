<?php

namespace App\Domain\Notification\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum NotificationType: string
{
    use HasEnumValues;

    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case SUCCESS = 'success';

    public function label(): string
    {
        return match($this) {
            self::INFO => 'Информация',
            self::WARNING => 'Предупреждение',
            self::ERROR => 'Ошибка',
            self::SUCCESS => 'Успех',
        };
    }
}
