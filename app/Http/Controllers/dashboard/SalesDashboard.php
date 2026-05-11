<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetrics;
use App\Services\DashboardWeekVisitMap;
use Illuminate\View\View;

class SalesDashboard extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $metrics = DashboardMetrics::forUser($user);

        return view('content.dashboard.dashboards-sales', [
            'metrics' => $metrics,
            'isAdmin' => $user->canManageAllVisits(),
            'weekVisitMap' => DashboardWeekVisitMap::forUser($user),
        ]);
    }
}
