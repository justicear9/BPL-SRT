<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisitOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', VisitOrder::class);

        $query = VisitOrder::query()
            ->with(['visit.customer', 'visit.user', 'lines'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->whereHas('visit.customer', fn (Builder $q) => $q->where('name', 'like', $term));
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

        $orders = $query->paginate(40)->withQueryString();

        $base = VisitOrder::query();
        $listStats = [
            ['label' => __('Orders'), 'value' => (string) (clone $base)->count(), 'caption' => __('All time'), 'icon' => 'tabler-receipt', 'variant' => 'primary'],
            ['label' => __('This month'), 'value' => (string) (clone $base)->whereHas('visit', fn (Builder $q) => $q->where('visited_at', '>=', now()->startOfMonth()))->count(), 'caption' => __('Visit date'), 'icon' => 'tabler-calendar', 'variant' => 'success'],
            ['label' => __('With lines'), 'value' => (string) (clone $base)->whereHas('lines')->count(), 'caption' => __('Line items'), 'icon' => 'tabler-list', 'variant' => 'warning'],
            ['label' => __('Latest ID'), 'value' => (string) ((clone $base)->max('id') ?? 0), 'caption' => __('Most recent row'), 'icon' => 'tabler-hash', 'variant' => 'info'],
        ];

        $salesReps = $request->user()->canManageAllVisits()
            ? User::assignableFieldTeam()->get()
            : collect();

        return view('content.workspace.orders.index', compact('orders', 'listStats', 'salesReps'));
    }
}
