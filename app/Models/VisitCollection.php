<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitCollection extends Model
{
    protected $fillable = [
        'visit_id',
        'amount',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Stored keys (workspace + Filament).
     *
     * @return array<string, string>
     */
    public static function paymentMethodOptions(): array
    {
        return [
            'cash' => __('Cash'),
            'mobile_money' => __('Mobile money'),
            'bank_transfer' => __('Bank transfer'),
            'card' => __('Card'),
            'cheque' => __('Cheque'),
            'other' => __('Other'),
        ];
    }

    public function paymentMethodLabel(): string
    {
        $key = $this->payment_method;

        return ($key !== null && $key !== '')
            ? (self::paymentMethodOptions()[$key] ?? $key)
            : '';
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
