<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Filament requires a dashboard route at the panel root. The Vuexy sales overview at `/`
 * is the real home; this page stays off the sidebar so navigation + redirects prefer the overview link.
 */
class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;
}
