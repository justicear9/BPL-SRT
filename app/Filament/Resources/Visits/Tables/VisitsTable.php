<?php

namespace App\Filament\Resources\Visits\Tables;

use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'user',
                'customer',
                'contact',
                'order.lines',
                'samples',
                'collections',
            ]))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Rep')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_id')
                    ->label('Contact')
                    ->toggleable()
                    ->formatStateUsing(fn ($_, Visit $record): string => $record->contact?->listLabel() ?? '—'),
                TextColumn::make('customer.type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '')
                    ->toggleable(),
                TextColumn::make('visited_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_total')
                    ->label('Order total')
                    ->money(fn (): string => Setting::currencyCode())
                    ->getStateUsing(fn (Visit $record): float => $record->orderLineTotal()),
                TextColumn::make('sample_units')
                    ->label('Sample units')
                    ->numeric()
                    ->getStateUsing(fn (Visit $record): int => (int) $record->samples->sum('quantity')),
                TextColumn::make('collected')
                    ->label('Collected')
                    ->money(fn (): string => Setting::currencyCode())
                    ->getStateUsing(fn (Visit $record): float => (float) $record->collections->sum('amount')),
                TextColumn::make('comments')
                    ->limit(40)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState())
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Sales rep')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => auth()->user() instanceof User && auth()->user()->canManageAllVisits()),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
