<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->idioma_preferido ?? 'es';

        app()->setLocale(in_array($locale, ['es', 'en'], true) ? $locale : 'es');

        return $next($request);
    }
}
