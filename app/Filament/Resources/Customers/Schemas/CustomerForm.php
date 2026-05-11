<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assigned_user_id')
                    ->label('Assigned sales rep')
                    ->relationship('assignedUser', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->visible(fn (): bool => Auth::user()?->canManageAllVisits() ?? false),
                Select::make('type')
                    ->options(collect(CustomerType::cases())->mapWithKeys(
                        fn (CustomerType $type): array => [$type->value => $type->label()]
                    )->all())
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(64),
                TextInput::make('address_line')
                    ->maxLength(255),
                TextInput::make('city')
                    ->maxLength(120),
                TextInput::make('region')
                    ->maxLength(120),
            ]);
    }
}
