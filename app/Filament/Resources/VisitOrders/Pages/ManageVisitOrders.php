<?php

namespace App\Filament\Resources\VisitOrders\Pages;

use App\Filament\Resources\VisitOrders\VisitOrderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitOrders extends ManageRecords
{
    protected static string $resource = VisitOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
