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

        // Every FRA login must be bound to an employer (FRA) record — without
        // it the portal has no tenant to scope applicants to.
        if (! $user->employer_id) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('fra.login')->withErrors([
                'login' => 'This FRA account is not linked to an employer yet. Please contact the agency admin.',
            ]);
        }

        return $next($request);
    }
}
