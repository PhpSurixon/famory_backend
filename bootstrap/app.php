<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\IsAdvertiser;
use App\Http\Middleware\IsAdmin;
use  App\Http\Middleware\CheckBlocked;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api')->name('teacher.')->group(base_path('routes/new.php'));
        },
        
    )
    ->withMiddleware(function (Middleware $middleware) {
          $middleware->alias([
            'jwt.verify' => JWTMiddleware::class,
            'auth'=> Authenticate::class,
            'isAdvertiser'=> IsAdvertiser::class,
            'isAdmin'=> IsAdmin::class,
            'checkBlocked' => CheckBlocked::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
    