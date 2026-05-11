<?php

namespace App\Models;

use App\Rules\LocalTenDigitPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'position',
        'email',
        'is_primary',
    ];

    public function listLabel(): string
    {
        $parts = array_filter([$this->name, $this->phone, $this->position]);

        return implode(' · ', $parts);
    }

    protected function setPhoneAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['phone'] = null;

            return;
        }

        $digits = LocalTenDigitPhone::normalize($value);
        $this->attributes['phone'] = $digits === '' ? null : $digits;
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
