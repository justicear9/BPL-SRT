<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('default_unit_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix(fn (): string => Setting::currencySymbol()),
                Toggle::make('can_be_sampled')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
