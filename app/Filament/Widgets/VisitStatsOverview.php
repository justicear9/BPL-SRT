<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitCollection;
use App\Models\VisitOrderLine;
use App\Models\VisitSample;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class VisitStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    private const STATS_PERIOD_DAYS = 7;

    protected function getStats(): array
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return [];
        }

        $days = self::STATS_PERIOD_DAYS;

        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $visitScope = Visit::query()->whereBetween('visited_at', [$start, $end]);
        if (! $user->canManageAllVisits()) {
            $visitScope->where('user_id', $user->id);
        }

        $visitIds = (clone $visitScope)->pluck('id');
        $visitCount = (clone $visitScope)->count();

        $sym = Setting::currencySymbol();

        if ($visitIds->isEmpty()) {
            return [
                Stat::make("Visits ({$days} days)", '0'),
                Stat::make("Order value ({$days} days)", $sym.'0.00')
                    ->description('Sum of order line totals'),
                Stat::make("Sample units ({$days} days)", '0'),
                Stat::make("Collections ({$days} days)", $sym.'0.00')
                    ->description('Payments recorded on visits'),
            ];
        }

        $orderTotal = VisitOrderLine::query()
            ->whereHas('visitOrder', fn ($q) => $q->whereIn('visit_id', $visitIds))
            ->get()
            ->sum(fn ($line) => (float) $line->quantity * (float) $line->unit_price);

        $sampleUnits = VisitSample::query()
            ->whereIn('visit_id', $visitIds)
            ->sum('quantity');

        $collected = VisitCollection::query()
            ->whereIn('visit_id', $visitIds)
            ->sum('amount');

        return [
            Stat::make("Visits ({$days} days)", (string) $visitCount),
            Stat::make("Order value ({$days} days)", $sym.number_format((float) $orderTotal, 2))
                ->description('Sum of order line totals'),
            Stat::make("Sample units ({$days} days)", (string) $sampleUnits),
            Stat::make("Collections ({$days} days)", $sym.number_format((float) $collected, 2))
                ->description('Payments recorded on visits'),
        ];
    }
}
