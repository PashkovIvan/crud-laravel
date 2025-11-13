<?php

namespace App\Domain\User\Enums;

use App\Domain\Common\Traits\HasEnumValues;

enum UserRole: string
{
    use HasEnumValues;

    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case USER = 'user';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Администратор',
            self::MANAGER => 'Менеджер',
            self::USER => 'Пользователь',
        };
    }
}
