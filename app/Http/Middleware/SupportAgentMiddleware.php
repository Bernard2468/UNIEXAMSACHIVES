<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the Support Inbox to support agents only.
 *
 * The agent pool (see {@see \App\Models\User::isSupportAgent()}):
 *   - Super Admins,
 *   - Institutional Admins (UI "Admin", is_admin = false), and
 *   - active members of the designated Support office.
 */
class SupportAgentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('frontend.login')
                ->with('error', 'Please login to continue.');
        }

        if (!$user->isSupportAgent()) {
            abort(403, 'Only support agents may access the Support Inbox.');
        }

        return $next($request);
    }
}
