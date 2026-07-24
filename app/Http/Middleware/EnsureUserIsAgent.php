<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $agent = $request->user('agent');

        if (!$agent) {
            return redirect()->route('agent.login');
        }

        if ($agent->status !== 'active') {
            auth('agent')->logout();
            return redirect()->route('agent.login')->withErrors(['email' => 'Your account is deactivated.']);
        }

        return $next($request);
    }
}
