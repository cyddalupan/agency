<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSponsorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->user_type === 'sponsor') {
                return redirect()->route('sponsor.dashboard');
            }

            // For non-sponsor users on sponsor pages, still redirect away
            return redirect('/');
        }

        return $next($request);
    }
}
