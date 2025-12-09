<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Purge soft-deleted content older than recovery period (default: 30 days)
        $schedule->command('content:purge-deleted --force')
            ->daily()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/content-purge.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
