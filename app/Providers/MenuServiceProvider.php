<?php

namespace App\Providers;

use App\Services\SalesSidebarMenu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['layouts.contentNavbarLayout', 'layouts.horizontalLayout'], function (\Illuminate\View\View $view): void {
            $user = auth()->user();
            $pair = app(SalesSidebarMenu::class)->menuDataPair($user instanceof \App\Models\User ? $user : null);
            $view->with('menuData', $pair);
        });
    }
}
