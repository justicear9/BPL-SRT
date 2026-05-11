<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitCollection;
use App\Models\VisitOrder;
use App\Models\VisitOrderLine;
use App\Models\VisitSample;
use Illuminate\Support\Carbon;

class DashboardMetrics
{
    /**
     * @return array{
     *   period_days: int,
     *   currency_symbol: string,
     *   currency_code: string,
     *   total_visits: int,
     *   visits_with_orders: int,
     *   visits_with_collections: int,
     *   visits_with_sale_or_collection: int,
     *   total_sample_units: int,
     *   order_value_total: float,
     *   collections_total: float,
     *   chart_labels: list<string>,
     *   visits_by_day: list<int>,
     *   orders_value_by_day: list<float>,
     *   collections_by_day: list<float>,
     *   samples_by_day: list<int>,
     * }
     */
    public static function forUser(User $user, int $days = 7): array
    {
        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $visitQuery = Visit::query()->whereBetween('visited_at', [$start, $end]);
        if (! $user->canManageAllVisits()) {
            $visitQuery->where('user_id', $user->id);
        }

        $visitIds = (clone $visitQuery)->pluck('id');

        $totalVisits = (clone $visitQuery)->count();

        $visitsWithOrders = Visit::query()
            ->whereIn('id', $visitIds)
            ->whereHas('order.lines')
            ->count();

        $visitsWithCollections = Visit::query()
            ->whereIn('id', $visitIds)
            ->whereHas('collections')
            ->count();

        $visitsWithSaleOrCollection = Visit::query()
            ->whereIn('id', $visitIds)
            ->where(function ($q): void {
                $q->whereHas('order.lines')
                    ->orWhereHas('collections');
            })
            ->count();

        $totalSampleUnits = (int) VisitSample::query()
            ->whereIn('visit_id', $visitIds)
            ->sum('quantity');

        $orderValueTotal = 0.0;
        if ($visitIds->isNotEmpty()) {
            $orderIds = VisitOrder::query()->whereIn('visit_id', $visitIds)->pluck('id');
            if ($orderIds->isNotEmpty()) {
                $orderValueTotal = (float) VisitOrderLine::query()
                    ->whereIn('visit_order_id', $orderIds)
                    ->get()
                    ->sum(fn ($line) => (float) $line->quantity * (float) $line->unit_price);
            }
        }

        $collectionsTotal = (float) VisitCollection::query()
            ->whereIn('visit_id', $visitIds)
            ->sum('amount');

        $chartLabels = [];
        $visitsByDay = [];
        $ordersValueByDay = [];
        $collectionsByDay = [];
        $samplesByDay = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dayStart = Carbon::now()->subDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();
            $chartLabels[] = $dayStart->format('M j');

            $dayVisitIds = (clone $visitQuery)
                ->whereBetween('visited_at', [$dayStart, $dayEnd])
                ->pluck('id');

            $visitsByDay[] = $dayVisitIds->count();

            $samplesByDay[] = (int) VisitSample::query()
                ->whereIn('visit_id', $dayVisitIds)
                ->sum('quantity');

            $dayOrderValue = 0.0;
            if ($dayVisitIds->isNotEmpty()) {
                $dayOrderIds = VisitOrder::query()->whereIn('visit_id', $dayVisitIds)->pluck('id');
                if ($dayOrderIds->isNotEmpty()) {
                    $dayOrderValue = (float) VisitOrderLine::query()
                        ->whereIn('visit_order_id', $dayOrderIds)
                        ->get()
                        ->sum(fn ($line) => (float) $line->quantity * (float) $line->unit_price);
                }
            }
            $ordersValueByDay[] = round($dayOrderValue, 2);

            $collectionsByDay[] = (float) VisitCollection::query()
                ->whereIn('visit_id', $dayVisitIds)
                ->sum('amount');
        }

        return [
            'period_days' => $days,
            'currency_symbol' => Setting::currencySymbol(),
            'currency_code' => Setting::currencyCode(),
            'total_visits' => $totalVisits,
            'visits_with_orders' => $visitsWithOrders,
            'visits_with_collections' => $visitsWithCollections,
            'visits_with_sale_or_collection' => $visitsWithSaleOrCollection,
            'total_sample_units' => $totalSampleUnits,
            'order_value_total' => round($orderValueTotal, 2),
            'collections_total' => round($collectionsTotal, 2),
            'chart_labels' => $chartLabels,
            'visits_by_day' => $visitsByDay,
            'orders_value_by_day' => $ordersValueByDay,
            'collections_by_day' => $collectionsByDay,
            'samples_by_day' => $samplesByDay,
        ];
    }
}
