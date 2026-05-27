<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate routes to institutional administrators only.
 *
 * In this system's reversed role terminology:
 *   - Database role 'user' (is_admin = false) = UI "Admin" / Institutional Administrator
 *   - Database role 'admin' (is_admin = true) = UI "User" / Normal User
 *   - Database role 'super_admin'              = Super Admin (always allowed)
 *
 * Use this middleware on any route that should only be accessible to the
 * institutional admin tier (e.g. Offices, Positions if/when locked down,
 * other system configuration routes).
 */
class InstitutionalAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('frontend.login')
                ->with('error', 'Please login to continue.');
        }

        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isInstitutionalAdmin = !(bool) $user->is_admin;

        if (!$isSuperAdmin && !$isInstitutionalAdmin) {
            abort(403, 'Only institutional administrators may access this area.');
        }

        return $next($request);
    }
}
