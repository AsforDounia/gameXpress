<?php

use App\Http\Middleware\CheckProductManagerPermission;
use App\Http\Middleware\CheckUserManagerPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\ExpireCartItems;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.product.manager' => CheckProductManagerPermission::class,
            'check.user.manager' => CheckUserManagerPermission::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new ExpireCartItems)->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
