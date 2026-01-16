<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\Role;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => Role::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Auto-generate discipline votes daily at 1 AM for active period
        $schedule->call(function () {
            $activePeriod = \App\Models\Period::where('status', 'open')->first();
            if ($activePeriod) {
                \Illuminate\Support\Facades\Artisan::call('discipline:generate-votes', [
                    'period' => $activePeriod->id,
                    '--voter' => 1,
                ]);
            }
        })->dailyAt('01:00')->description('Generate automatic votes from discipline scores for Pegawai Disiplin category');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
