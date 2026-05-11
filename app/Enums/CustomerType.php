<?php

namespace App\Enums;

enum CustomerType: string
{
    case Pharmacy = 'pharmacy';
    case Hospital = 'hospital';
    case Wholesaler = 'wholesaler';
    case ChemicalShop = 'chemical_shop';

    public function label(): string
    {
        return match ($this) {
            self::Pharmacy => 'Pharmacy',
            self::Hospital => 'Hospital',
            self::Wholesaler => 'Wholesale reseller',
            self::ChemicalShop => 'Chemical shop',
        };
    }
}
