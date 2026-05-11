<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Exports\VisitExporter;
use App\Filament\Resources\Visits\VisitResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(VisitExporter::class)
                ->modifyQueryUsing(function (Builder $query): Builder {
                    $user = auth()->user();
                    if ($user instanceof User && ! $user->canManageAllVisits()) {
                        $query->where('visits.user_id', $user->id);
                    }

                    return $query->with(['user', 'customer', 'order.lines', 'samples', 'collections']);
                }),
        ];
    }
}
