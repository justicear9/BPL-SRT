<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Concerns\CapturesVisitGeolocation;
use App\Filament\Resources\Visits\VisitResource;
use App\Services\CustomerShopLocation;
use App\Services\VisitNestedWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateVisit extends CreateRecord
{
    use CapturesVisitGeolocation;

    protected static string $resource = VisitResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['order_lines_block'], $data['samples_block'], $data['collections_block'], $data['geolocation_hint']);

        $user = Auth::user();
        if ($user && ! $user->canManageAllVisits()) {
            $data['user_id'] = $user->id;
        }

        if ($user && ! $user->canManageAllVisits()) {
            $data['visited_at'] = now();
        }

        if ($this->captured_latitude !== null && $this->captured_longitude !== null) {
            $data['visit_latitude'] = $this->captured_latitude;
            $data['visit_longitude'] = $this->captured_longitude;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        VisitNestedWriter::sync(
            $this->record,
            $state['order_lines_block'] ?? [],
            $state['samples_block'] ?? [],
            $state['collections_block'] ?? [],
        );

        $this->record->refresh();
        CustomerShopLocation::syncFromVisit($this->record);
    }
}
