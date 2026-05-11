<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Visit;
use App\Models\VisitOrder;
use App\Models\VisitOrderLine;
use Illuminate\Support\Facades\DB;

class VisitNestedWriter
{
    /**
     * @param  array<int, array{product_id?: int|null, quantity?: int|null, unit_price?: mixed}>  $orderLines
     * @param  array<int, array{product_id?: int|null, quantity?: int|null}>  $samples
     * @param  array<int, array{amount?: mixed, payment_method?: string|null, notes?: string|null}>  $collections
     */
    public static function sync(Visit $visit, array $orderLines, array $samples, array $collections): void
    {
        DB::transaction(function () use ($visit, $orderLines, $samples, $collections): void {
            if ($visit->order) {
                $visit->order->lines()->delete();
                $visit->order->delete();
            }

            $validLines = array_values(array_filter(
                $orderLines,
                fn (array $row): bool => ! empty($row['product_id']) && ! empty($row['quantity'])
            ));

            if ($validLines !== []) {
                $order = VisitOrder::create(['visit_id' => $visit->id]);
                foreach ($validLines as $row) {
                    $product = Product::query()->find($row['product_id']);
                    $unitPrice = $row['unit_price'] ?? $product?->default_unit_price ?? 0;
                    VisitOrderLine::query()->create([
                        'visit_order_id' => $order->id,
                        'product_id' => (int) $row['product_id'],
                        'quantity' => max(1, (int) $row['quantity']),
                        'unit_price' => $unitPrice,
                    ]);
                }
            }

            $visit->samples()->delete();
            foreach ($samples as $row) {
                if (empty($row['product_id']) || empty($row['quantity'])) {
                    continue;
                }
                $visit->samples()->create([
                    'product_id' => (int) $row['product_id'],
                    'quantity' => max(1, (int) $row['quantity']),
                ]);
            }

            $visit->collections()->delete();
            foreach ($collections as $row) {
                if (! isset($row['amount']) || $row['amount'] === '' || $row['amount'] === null) {
                    continue;
                }
                $method = $row['payment_method'] ?? null;
                $method = ($method !== null && $method !== '') ? (string) $method : null;

                $visit->collections()->create([
                    'amount' => $row['amount'],
                    'payment_method' => $method,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        });
    }
}
