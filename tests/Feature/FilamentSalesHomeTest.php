<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Tests\TestCase;

class FilamentSalesHomeTest extends TestCase
{
    public function test_filament_panel_home_url_points_at_sales_overview_route(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertSame(route('dashboard-sales', absolute: true), $panel->getHomeUrl());
    }
}
