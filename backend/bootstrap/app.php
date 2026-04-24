<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- THIS LINE MUST BE HERE
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->append(\App\Http\Middleware\Cors::class);
         $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'customer'=> \App\Http\Middleware\CustomerMiddleware::class,
        ]);
        
        // Configure authentication redirects
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            if ($request->is('staff/*')) {
                return route('staff.login');
            }
            return route('welcome');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
