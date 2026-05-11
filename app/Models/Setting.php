<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const KEY_CURRENCY_SYMBOL = 'currency_symbol';

    public const KEY_CURRENCY_CODE = 'currency_code';

    /**
     * ISO 4217 codes supported in workspace settings (label => is code key for select value).
     *
     * @return array<string, string>
     */
    public static function currencyCodeOptions(): array
    {
        return [
            'USD' => 'US Dollar (USD)',
            'GHS' => 'Ghana Cedi (GHS)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'CAD' => 'Canadian Dollar (CAD)',
            'AUD' => 'Australian Dollar (AUD)',
            'JPY' => 'Japanese Yen (JPY)',
            'CHF' => 'Swiss Franc (CHF)',
            'INR' => 'Indian Rupee (INR)',
            'MXN' => 'Mexican Peso (MXN)',
            'BRL' => 'Brazilian Real (BRL)',
        ];
    }

    /** @var array<string, string>|null */
    protected static ?array $cache = null;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        self::loadCache();

        return self::$cache[$key] ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        self::$cache = null;
    }

    public static function currencySymbol(): string
    {
        return self::getValue(self::KEY_CURRENCY_SYMBOL, '₵') ?? '₵';
    }

    public static function currencyCode(): string
    {
        return self::getValue(self::KEY_CURRENCY_CODE, 'GHS') ?? 'GHS';
    }

    protected static function loadCache(): void
    {
        if (self::$cache !== null) {
            return;
        }
        self::$cache = self::query()->pluck('value', 'key')->all();
    }
}
