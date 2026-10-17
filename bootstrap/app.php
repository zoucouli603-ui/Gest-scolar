<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSchoolAdmin;
use App\Http\Middleware\EnsureSchoolSettingsAccess;
use App\Http\Middleware\EnsureSchoolTeamAccess;
use App\Http\Middleware\EnsureSchoolTenant;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ClearSchoolTenantContext;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ClearSchoolTenantContext::class);
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            EnsureSchoolTenant::class
        );
        $middleware->alias([
            'school.tenant' => EnsureSchoolTenant::class,
            'school.admin' => EnsureSchoolAdmin::class,
            'school.team.access' => EnsureSchoolTeamAccess::class,
            'school.settings.access' => EnsureSchoolSettingsAccess::class,
            'school.access' => \App\Http\Middleware\EnsureModuleAccess::class,
            'super.admin' => EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
