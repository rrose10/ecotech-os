<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /** Routes a user may still use while a password change is pending. */
    private const ALLOWED = ['password.force', 'password.force.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !$request->routeIs(self::ALLOWED)) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}
