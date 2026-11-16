<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

        $request->session()->put('locale', $locale);

        // Remember the choice for a year, also after logging out
        return back()->withCookie(Cookie::make('locale', $locale, 60 * 24 * 365));
    }
}
