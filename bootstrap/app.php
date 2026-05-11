<?php

use App\Http\Middleware\LocaleMiddleware;
use App\Livewire\AbsoluteUriHandleRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->booting(function (Application $app): void {
        $app->forgetInstance(HandleRequests::class);

        $app->singleton(HandleRequests::class, fn (): AbsoluteUriHandleRequests => new AbsoluteUriHandleRequests);
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): string => route('filament.admin.auth.login'));

        $middleware->web(append: [
            LocaleMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
