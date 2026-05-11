<?php

namespace App\Filament\Exports;

use App\Models\Visit;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class VisitExporter extends Exporter
{
    protected static ?string $model = Visit::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Rep'),
            ExportColumn::make('customer.name')
                ->label('Customer'),
            ExportColumn::make('customer.type')
                ->label('Customer type')
                ->getStateUsing(fn (Visit $record): string => $record->customer?->type?->label() ?? ''),
            ExportColumn::make('visited_at'),
            ExportColumn::make('comments'),
            ExportColumn::make('order_total')
                ->label('Order total')
                ->getStateUsing(fn (Visit $record): string => number_format($record->orderLineTotal(), 2, '.', '')),
            ExportColumn::make('sample_units')
                ->label('Sample units')
                ->getStateUsing(fn (Visit $record): int => (int) $record->samples->sum('quantity')),
            ExportColumn::make('collected')
                ->label('Collections total')
                ->getStateUsing(fn (Visit $record): string => number_format((float) $record->collections->sum('amount'), 2, '.', '')),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your visit export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
