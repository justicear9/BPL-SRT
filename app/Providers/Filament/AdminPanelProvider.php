<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login as FilamentLogin;
use App\Filament\Http\Responses\LoginResponse as AppLoginResponse;
use App\Filament\Widgets\VisitStatsOverview;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->bind(LoginResponseContract::class, AppLoginResponse::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(FilamentLogin::class)
            ->brandLogo(asset('assets/img/branding/logo-full.png'))
            ->brandLogoHeight('1.75rem')
            ->brandName('Bedita Pharmaceuticals')
            ->homeUrl(fn (): string => route('dashboard-sales', absolute: true))
            ->navigationItems([
                NavigationItem::make('Sales overview')
                    ->url(fn (): string => route('dashboard-sales', absolute: true))
                    ->icon(Heroicon::OutlinedChartBar)
                    ->sort(-100),
            ])
            ->colors([
                'primary' => Color::hex('#C8102E'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                VisitStatsOverview::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
