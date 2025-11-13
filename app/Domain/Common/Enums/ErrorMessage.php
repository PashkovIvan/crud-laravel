<?php

namespace App\Domain\Common\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum ErrorMessage: string
{
    use HasEnumValues;

    case VALIDATION_ERROR = 'Ошибка валидации данных';
    case UNAUTHORIZED = 'Неверные учетные данные';
    case FORBIDDEN = 'Доступ запрещен';
    case NOT_FOUND = 'Ресурс не найден';
    case SERVER_ERROR = 'Произошла ошибка при выполнении операции';
    case TASK_NOT_FOUND = 'Задача не найдена';
    case USER_NOT_FOUND = 'Пользователь не найден';
    case NOTIFICATION_NOT_FOUND = 'Уведомление не найдено';
    case ADMIN_ACCESS_REQUIRED = 'Доступ запрещен. Требуются права администратора.';

    public function label(): string
    {
        return $this->value;
    }
}
