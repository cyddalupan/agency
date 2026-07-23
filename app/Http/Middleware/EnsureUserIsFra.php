<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsFra
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('fra.login');
        }

        if ($user->user_type !== 'employer') {
            return redirect()->route('fra.login');
        }

        return $next($request);
    }
}
