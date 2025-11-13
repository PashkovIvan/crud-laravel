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
        try {
            return (int) Crypt::decryptString($encryptedId);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Некорректный формат зашифрованного ID');
        }
    }

    public static function tryDecrypt(?string $encryptedId): ?int
    {
        if ($encryptedId === null) {
            return null;
        }

        try {
            return self::decrypt($encryptedId);
        } catch (Exception $e) {
            return null;
        }
    }
}
