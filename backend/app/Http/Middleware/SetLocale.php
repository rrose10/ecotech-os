<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the interface language (English or Bangla) from the session,
 * falling back to the "locale" cookie, so it survives logging out.
 */
class SetLocale
{
    public const LOCALES = ['en', 'bn'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;
        $locale = $locale ?: $request->cookie('locale');

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
