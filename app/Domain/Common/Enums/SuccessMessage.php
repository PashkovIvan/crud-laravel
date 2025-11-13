<?php

namespace App\Domain\Common\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum SuccessMessage: string
{
    use HasEnumValues;

    case REGISTRATION_SUCCESS = 'Регистрация успешна';
    case LOGIN_SUCCESS = 'Вход выполнен успешно';
    case LOGOUT_SUCCESS = 'Успешный выход из системы';
    case TASK_CREATED = 'Задача успешно создана';
    case TASK_UPDATED = 'Задача успешно обновлена';
    case TASK_DELETED = 'Задача успешно удалена';
    case TASK_COMPLETED = 'Задача отмечена как завершенная';
    case TASK_IN_PROGRESS = 'Задача отмечена как в работе';
    case TASK_PENDING = 'Задача отмечена как в ожидании';
    case TASK_ASSIGNED = 'Задача успешно назначена';
    case NOTIFICATION_CREATED = 'Уведомление успешно создано';
    case NOTIFICATION_READ = 'Уведомление отмечено как прочитанное';
    case NOTIFICATIONS_ALL_READ = 'Все уведомления отмечены как прочитанные';

    public function label(): string
    {
        return $this->value;
    }
}
