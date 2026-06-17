<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Super admin and admin have full access
        if (in_array($user->user_type, ['super_admin', 'admin'])) {
            return $next($request);
        }

        if (!in_array($user->user_type, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
