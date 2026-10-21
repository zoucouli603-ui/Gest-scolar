<?php

namespace App\Providers;

use App\Tenancy\CurrentSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentSchool::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('cspNonce', function (): string {
            return 'nonce="'.e(request()->attributes->get('csp_nonce')).'"';
        });

        RateLimiter::for('sensitive', function (Request $request): Limit {
            $routeName = (string) $request->route()?->getName();
            $key = ($request->user()?->getAuthIdentifier() ?? 'guest')
                .'|'.$request->ip().'|'.$routeName;

            return Limit::perMinute(30)->by($key);
        });
        RateLimiter::for('registration', function (Request $request): array {
            $routeName = (string) $request->route()?->getName();
            $ip = $request->ip();
            $email = mb_strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($routeName.'|'.$email.'|'.$ip),
                Limit::perMinute(20)->by($routeName.'|'.$ip),
            ];
        });
        RateLimiter::for('password-reset', function (Request $request): array {
            $routeName = (string) $request->route()?->getName();
            $ip = $request->ip();
            $email = mb_strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(3)->by($routeName.'|'.$email.'|'.$ip),
                Limit::perMinute(10)->by($routeName.'|'.$ip),
            ];
        });

        $this->app->booted(function (): void {
            foreach (Route::getRoutes() as $route) {
                if (array_intersect($route->methods(), ['POST', 'PUT', 'PATCH', 'DELETE']) === []) {
                    continue;
                }

                $limiter = match ($route->getName()) {
                    'register.store',
                    'school.invitations.accept',
                    'school.setup.store',
                    'school.team.invitations.store' => 'registration',
                    'password.email' => 'password-reset',
                    default => 'sensitive',
                };

                $route->middleware('throttle:'.$limiter);
            }
        });
    }
}
