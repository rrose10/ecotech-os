<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! in_array(auth()->user()->role, $roles, true)) {

            // Employees who open an admin URL go to their own portal
            if (auth()->user()->isEmployee() && $request->isMethod('GET')) {
                return redirect()->route('portal.dashboard');
            }

            // HR users who open a finance/admin URL go back to their workspace
            if (auth()->user()->role === 'hr' && $request->isMethod('GET')) {
                return redirect()->route('employees.index');
            }

            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}