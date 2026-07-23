<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSponsor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('sponsor.login');
        }

        if ($user->user_type !== 'sponsor') {
            return redirect()->route('sponsor.login');
        }

        return $next($request);
    }
}
