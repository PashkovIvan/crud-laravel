<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoXss implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $original = $value;
        $sanitized = strip_tags($value);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');

        if ($original !== $sanitized) {
            $fail('Поле :attribute содержит недопустимые символы или теги.');
        }

        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<img[^>]+src[^>]*=.*javascript:/i',
            '/<link[^>]+href[^>]*=.*javascript:/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('Поле :attribute содержит потенциально опасный контент.');
                break;
            }
        }
    }
}
