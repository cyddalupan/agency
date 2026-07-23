<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = Auth::user()) {
            $locale = $user->locale ?? config('app.locale');
        } elseif ($locale = session('locale')) {
            // Guest pages can have locale set via the language switcher
            App::setLocale($locale);
            return $next($request);
        } else {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
