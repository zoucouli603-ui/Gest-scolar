<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentSchool;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearSchoolTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        app(CurrentSchool::class)->set(null);

        return $next($request);
    }
}
