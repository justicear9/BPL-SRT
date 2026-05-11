<?php

namespace App\Services;

use App\Models\Contact;
use App\Rules\LocalTenDigitPhone;
use InvalidArgumentException;

class VisitContactResolver
{
    public static function createContact(int $customerId, string $name, string $phone, string $position): Contact
    {
        $digits = LocalTenDigitPhone::normalize($phone);
        if ($digits === '' || ! preg_match('/^0\d{9}$/', $digits)) {
            throw new InvalidArgumentException(__('The phone number must be exactly 10 digits, numbers only, starting with 0.'));
        }

        return Contact::query()->create([
            'customer_id' => $customerId,
            'name' => $name,
            'phone' => $digits,
            'position' => $position,
            'is_primary' => false,
        ]);
    }
}
