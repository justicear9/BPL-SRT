<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisitCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = VisitCollection::query()
            ->with(['visit.customer', 'visit.user']);

        $user = auth()->user();
        if ($user instanceof User && ! $user->canManageAllVisits()) {
            $query->whereHas('visit', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('from')) {
            $from = $request->date('from')->toDateString();
            $query->whereHas('visit', fn (Builder $q) => $q->whereDate('visited_at', '>=', $from));
        }

        if ($request->filled('to')) {
            $to = $request->date('to')->toDateString();
            $query->whereHas('visit', fn (Builder $q) => $q->whereDate('visited_at', '<=', $to));
        }

        if ($request->filled('user_id') && $request->user()->canManageAllVisits()) {
            $query->whereHas('visit', fn (Builder $q) => $q->where('user_id', $request->integer('user_id')));
        }

        $collections = $query->orderByDesc('id')->paginate(50)->withQueryString();

        $salesReps = $request->user()->canManageAllVisits()
            ? User::assignableFieldTeam()->get()
            : collect();

        return view('content.reports.collection-report', [
            'collections' => $collections,
            'salesReps' => $salesReps,
        ]);
    }
}
