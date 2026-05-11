<?php

namespace App\Services;

use App\Models\Visit;

class CustomerShopLocation
{
    public static function syncFromVisit(Visit $visit): void
    {
        if ($visit->visit_latitude === null || $visit->visit_longitude === null) {
            return;
        }

        $visit->customer?->update([
            'shop_latitude' => $visit->visit_latitude,
            'shop_longitude' => $visit->visit_longitude,
        ]);
    }
}
