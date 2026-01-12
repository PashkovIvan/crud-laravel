<?php

namespace App\Domain\Common\Traits;

// problem: не вижу использования, хотя по коду где-то есть места, где должно использоваться 200%
trait HasEnumValues
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
