<?php

namespace App\Services;

use App\Models\User;
use App\Models\Visit;
use Carbon\WeekDay;
use Illuminate\Support\Carbon;

/**
 * Visit GPS pins for the current calendar week (Monday 00:00 → Sunday 23:59:59, app timezone).
 * Intended for admins viewing all reps; returns null for non-admins.
 */
class DashboardWeekVisitMap
{
    /**
     * @return array{
     *   week_label: string,
     *   week_start: string,
     *   week_end: string,
     *   visit_count: int,
     *   points: list<array{lat: float, lng: float, label: string, visited_at: string}>
     * }|null
     */
    public static function forUser(User $user): ?array
    {
        if (! $user->isAdmin()) {
            return null;
        }

        $start = Carbon::now()->copy()->startOfWeek(WeekDay::Monday)->startOfDay();
        $end = Carbon::now()->copy()->endOfWeek(WeekDay::Sunday)->endOfDay();

        $query = Visit::query()
            ->with(['customer:id,name', 'user:id,name'])
            ->whereBetween('visited_at', [$start, $end])
            ->whereNotNull('visit_latitude')
            ->whereNotNull('visit_longitude')
            ->orderBy('visited_at');

        $points = [];
        foreach ($query->cursor() as $visit) {
            $lat = (float) $visit->visit_latitude;
            $lng = (float) $visit->visit_longitude;
            if ($lat === 0.0 && $lng === 0.0) {
                continue;
            }

            $customer = $visit->customer?->name ?? '—';
            $rep = $visit->user?->name ?? '—';
            $when = $visit->visited_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '';

            $points[] = [
                'lat' => $lat,
                'lng' => $lng,
                'label' => $customer.' · '.$rep.' · '.$when,
                'visited_at' => $when,
            ];
        }

        $weekLabel = $start->isoFormat('MMM D').' – '.$end->isoFormat('MMM D, YYYY');

        return [
            'week_label' => $weekLabel,
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'visit_count' => count($points),
            'points' => $points,
        ];
    }
}
