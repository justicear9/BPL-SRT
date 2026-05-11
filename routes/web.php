<?php

use App\Http\Controllers\dashboard\SalesDashboard;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\NavigationSearchController;
use App\Http\Controllers\Reports\AlphaReportController;
use App\Http\Controllers\Reports\CollectionReportController;
use App\Http\Controllers\Reports\OrderReportController;
use App\Http\Controllers\Reports\SampleReportController;
use App\Http\Controllers\Reports\VisitReportController;
use App\Http\Controllers\Workspace\AppSettingsController;
use App\Http\Controllers\Workspace\CustomerContactController;
use App\Http\Controllers\Workspace\CustomerController;
use App\Http\Controllers\Workspace\OrderController;
use App\Http\Controllers\Workspace\ProductController;
use App\Http\Controllers\Workspace\ProfileController;
use App\Http\Controllers\Workspace\UserController;
use App\Http\Controllers\Workspace\VisitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', [SalesDashboard::class, 'index'])->name('dashboard-sales');

    Route::get('navigation-search.json', NavigationSearchController::class)->name('navigation.search');

    Route::prefix('reports')->name('reports.')->group(function (): void {
        Route::get('/visits', [VisitReportController::class, 'index'])->name('visits');
        Route::get('/orders', [OrderReportController::class, 'index'])->name('orders');
        Route::get('/samples', [SampleReportController::class, 'index'])->name('samples');
        Route::get('/collections', [CollectionReportController::class, 'index'])->name('collections');
        Route::get('/alpha', [AlphaReportController::class, 'index'])->name('alpha');
    });

    Route::prefix('workspace')->name('workspace.')->group(function (): void {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('admin')->group(function (): void {
            Route::get('customers/import/template', [CustomerController::class, 'importTemplate'])->name('customers.import.template');
            Route::get('customers/import', [CustomerController::class, 'importForm'])->name('customers.import');
            Route::post('customers/import', [CustomerController::class, 'importStore'])->name('customers.import.store');
        });

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::post('customers/{customer}/contacts', [CustomerContactController::class, 'store'])
            ->name('customers.contacts.store');
        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
        Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
        Route::get('visits/{visit}/modal', [VisitController::class, 'modal'])->name('visits.modal');
        Route::get('visits/{visit}/edit', [VisitController::class, 'edit'])->name('visits.edit');
        Route::put('visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
        Route::get('visits', [VisitController::class, 'index'])->name('visits.index');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

        Route::middleware('admin')->group(function (): void {
            Route::get('settings', [AppSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [AppSettingsController::class, 'update'])->name('settings.update');
        });
    });
});

Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
