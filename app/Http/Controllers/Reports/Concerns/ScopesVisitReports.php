<?php

namespace App\Http\Controllers\Reports\Concerns;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;

trait ScopesVisitReports
{
    protected function visitsBase(): Builder
    {
        $query = Visit::query()
            ->with(['customer', 'user', 'contact', 'order.lines', 'samples.product']);

        $user = auth()->user();
        if ($user instanceof User && ! $user->canManageAllVisits()) {
            $query->where('user_id', $user->id);
        }

        return $query->orderByDesc('visited_at');
    }
}
