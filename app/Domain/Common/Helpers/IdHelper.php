<?php

namespace App\Domain\Common\Helpers;

use Exception;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class IdHelper
{
    public static function encrypt(int $id): string
    {
        return Crypt::encryptString((string) $id);
    }

    public static function decrypt(string $encryptedId): int
    {
        // problem: может не нужен тут try catch?
        // problem: опасный текст, если он уходит наружу
        try {
            return (int) Crypt::decryptString($encryptedId);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Некорректный формат зашифрованного ID');
        }
    }
}
