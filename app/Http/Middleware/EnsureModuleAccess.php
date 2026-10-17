<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = (string) $request->route()?->getName();

        if (!$user || $user->isSuperAdmin() || $name === '') {
            return $next($request);
        }

        $module = explode('.', $name)[0];

        if (!in_array($module, \App\Models\User::MODULES, true) || $user->canAccess($module)) {
            return $next($request);
        }

        if ($module === 'dashboard') {
            return redirect()->route($user->homeRoute());
        }

        abort(403, 'Votre rôle ne donne pas accès à cette section.');
    }
}
