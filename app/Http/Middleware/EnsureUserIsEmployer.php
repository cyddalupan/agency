<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEmployer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('employer.login');
        }

        if ($user->user_type !== 'employer') {
            return redirect()->route('employer.login');
        }

        return $next($request);
    }
}
