<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Concerns\CapturesVisitGeolocation;
use App\Filament\Resources\Visits\VisitResource;
use App\Services\CustomerShopLocation;
use App\Services\VisitNestedWriter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditVisit extends EditRecord
{
    use CapturesVisitGeolocation;

    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $visit = $this->getRecord();
        $visit->load(['order.lines', 'samples', 'collections']);

        $data['order_lines_block'] = $visit->order?->lines
            ->map(fn ($line): array => [
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
            ])
            ->values()
            ->all() ?? [];

        $data['samples_block'] = $visit->samples
            ->map(fn ($sample): array => [
                'product_id' => $sample->product_id,
                'quantity' => $sample->quantity,
            ])
            ->values()
            ->all();

        $data['collections_block'] = $visit->collections
            ->map(fn ($collection): array => [
                'amount' => $collection->amount,
                'payment_method' => $collection->payment_method,
                'notes' => $collection->notes,
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['order_lines_block'], $data['samples_block'], $data['collections_block'], $data['geolocation_hint']);

        $user = Auth::user();
        if ($user && ! $user->canManageAllVisits()) {
            $data['visited_at'] = $this->record->visited_at;
        }

        $data['visit_latitude'] = $this->captured_latitude ?? $this->record->visit_latitude;
        $data['visit_longitude'] = $this->captured_longitude ?? $this->record->visit_longitude;

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        VisitNestedWriter::sync(
            $this->record->fresh(),
            $state['order_lines_block'] ?? [],
            $state['samples_block'] ?? [],
            $state['collections_block'] ?? [],
        );

        $this->record->refresh();
        CustomerShopLocation::syncFromVisit($this->record);
    }
}
