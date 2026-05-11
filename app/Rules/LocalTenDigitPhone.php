<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LocalTenDigitPhone implements ValidationRule
{
    public static function normalize(mixed $value): string
    {
        return preg_replace('/\D/', '', is_scalar($value) ? (string) $value : '');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = self::normalize($value);
        if ($digits === '' || ! preg_match('/^0\d{9}$/', $digits)) {
            $fail(__('The phone number must be exactly 10 digits, numbers only, starting with 0.'));
        }
    }
}
