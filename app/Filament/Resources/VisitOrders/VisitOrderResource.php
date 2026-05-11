<?php

namespace App\Filament\Resources\VisitOrders;

use App\Filament\Resources\VisitOrders\Pages\ManageVisitOrders;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VisitOrderResource extends Resource
{
    protected static ?string $model = VisitOrder::class;

    protected static ?string $navigationLabel = 'Orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 25;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ! $user->isSalesRep();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('visit.visited_at')
                    ->label('Visit date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('visit.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('visit.user.name')
                    ->label('Rep')
                    ->sortable(),
                TextColumn::make('line_total')
                    ->label('Order total')
                    ->money(fn (): string => Setting::currencyCode())
                    ->getStateUsing(fn (VisitOrder $record): float => $record->lineTotal()),
                TextColumn::make('visit_id')
                    ->label('Visit')
                    ->url(fn (VisitOrder $record): string => route('workspace.visits.edit', ['visit' => $record->visit_id], absolute: true))
                    ->formatStateUsing(fn (): string => 'Open visit'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVisitOrders::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['visit.customer', 'visit.user', 'lines']);

        $user = auth()->user();
        if ($user instanceof User && ! $user->canManageAllVisits()) {
            $query->whereHas('visit', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return $query;
    }
}
