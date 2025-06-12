<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'checkPermission' => \App\Http\Middleware\CheckPermission::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //  if($exceptions instanceof UnauthorizedHttpException) {
        //     return response()->json(['message' => $exceptions->getMessage()], 403);
        // }
    })->create();
