<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
| Domínio único (SaaS): a empresa é resolvida pela conta autenticada
| (ver App\Http\Middleware\InitializeTenancyForAccount), não por subdomínio.
| As rotas da aplicação vivem em routes/web.php sob o prefixo /app.
*/
