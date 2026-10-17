<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Tenancy\CurrentSchool;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(CurrentSchool::class);
        $context->set(null);
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('owner.dashboard');
        }

        $school = $user->school;
        if (!$school) {
            return redirect()->route('school.setup.create');
        }

        $context->set($school);
        $school->loadMissing('currentSchoolYear');
        view()->share('currentSchool', $school);
        view()->share('currency', $school->currency);

        if ($school->access_status === 'suspended') {
            abort(503, 'L’accès à cet établissement est suspendu par le super-administrateur.');
        }

        if ($school->maintenance_mode && !$user->isSchoolAdmin()) {
            abort(503, 'L’accès à cet établissement est temporairement suspendu par son administrateur.');
        }

        if (!$school->last_activity_at || $school->last_activity_at->lt(now()->subMinutes(5))) {
            School::whereKey($school->id)->update(['last_activity_at' => now()]);
            $school->last_activity_at = now();
        }

        return $next($request);
    }
}
