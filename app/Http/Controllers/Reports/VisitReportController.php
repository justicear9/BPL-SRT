<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\Concerns\ScopesVisitReports;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitReportController extends Controller
{
    use ScopesVisitReports;

    public function index(Request $request): View
    {
        $query = $this->visitsBase();

        if ($request->filled('from')) {
            $query->whereDate('visited_at', '>=', $request->date('from')->toDateString());
        }

        if ($request->filled('to')) {
            $query->whereDate('visited_at', '<=', $request->date('to')->toDateString());
        }

        if ($request->filled('user_id') && $request->user()->canManageAllVisits()) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $visits = $query->paginate(40)->withQueryString();

        $salesReps = $request->user()->canManageAllVisits()
            ? User::assignableFieldTeam()->get()
            : collect();

        return view('content.reports.visit-report', [
            'visits' => $visits,
            'salesReps' => $salesReps,
        ]);
    }
}
