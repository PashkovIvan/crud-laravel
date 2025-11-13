<?php

namespace App\Domain\Common\Traits;

trait HasEnumValues
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
