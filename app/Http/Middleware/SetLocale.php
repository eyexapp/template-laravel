<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $request->header('Accept-Language');

        if ($locale && in_array($locale, ['en', 'tr'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
