<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Destino de redireccionamento conforme a área (empresa vs back-office).
        $isAdmin = fn (\Illuminate\Http\Request $request) => $request->is('admin', 'admin/*');
        $middleware->redirectUsersTo(fn ($request) => $isAdmin($request) ? '/admin' : '/app');
        $middleware->redirectGuestsTo(fn ($request) => $isAdmin($request) ? '/admin/login' : route('login'));

        $middleware->alias([
            'tenant' => \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
            'tenant.path' => \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
            'module' => \App\Http\Middleware\EnsureModuleIsActive::class,
            'hmac' => \App\Http\Middleware\VerifyProxyPayHmac::class,
            'tenant.account' => \App\Http\Middleware\InitializeTenancyForAccount::class,
        ]);

        // Callbacks ProxyPay são server-to-server → isentar de CSRF.
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
