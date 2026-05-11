<?php

namespace App\Filament\Concerns;

trait CapturesVisitGeolocation
{
    public ?float $captured_latitude = null;

    public ?float $captured_longitude = null;

    public function setCapturedLocation(?float $lat, ?float $lng): void
    {
        $this->captured_latitude = $lat;
        $this->captured_longitude = $lng;
    }
}
