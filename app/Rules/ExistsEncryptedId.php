<?php

namespace App\Rules;

use App\Domain\Common\Helpers\IdHelper;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistsEncryptedId implements ValidationRule
{
    public function __construct(
        private string $model,
        private string $column = 'id'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('Поле :attribute должно быть строкой.');

            return;
        }

        try {
            $decryptedId = IdHelper::decrypt($value);

            // problem: можно любую модель прокинуть сюда
            $exists = $this->model::where($this->column, $decryptedId)->exists();

            if (!$exists) {
                $fail('Выбранный :attribute не существует.');
            }
        } catch (Exception $e) {
            $fail('Некорректный формат :attribute.');
        }
    }
}
