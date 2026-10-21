<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    private const UNSANITIZED_FIELDS = [
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'two_factor_code',
        'recovery_code',
        'token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $request->query->replace($this->sanitize($request->query->all()));
        $request->request->replace($this->sanitize($request->request->all()));

        if ($request->isJson()) {
            $request->json()->replace($this->sanitize($request->json()->all()));
        }

        return $next($request);
    }

    private function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->sanitize($value);
            } elseif (
                is_string($value)
                && !in_array((string) $key, self::UNSANITIZED_FIELDS, true)
            ) {
                abort_unless(mb_check_encoding($value, 'UTF-8'), 400, 'Entrée malformée.');
                $input[$key] = trim(strip_tags($value));
            }
        }

        return $input;
    }
}
