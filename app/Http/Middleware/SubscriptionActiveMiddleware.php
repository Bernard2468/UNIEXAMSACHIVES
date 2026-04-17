<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSubscription;
use Illuminate\Support\Facades\View;

class SubscriptionActiveMiddleware
{
    /**
     * Routes that should be accessible even when subscription is expired
     */
    private array $exemptRoutes = [
        'frontend.login',
        'login',
        'register',
        'logout',
        'password.*',
        'super-admin.*', // Super admin routes (separate system)
        'dashboard.profile',
        'dashboard.settings',
        'subscription.locked', // Subscription locked page
        'subscription.subscribe', // Subscription creation/payment
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check if user is not authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        // Super admins bypass subscription checks
        if (auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Check if current route is exempt
        foreach ($this->exemptRoutes as $exemptRoute) {
            if ($request->routeIs($exemptRoute)) {
                return $next($request);
            }
        }

        // Get active subscription
        $subscription = SystemSubscription::active()->first();

        // If no active subscription, check for grace period
        if (!$subscription) {
            $subscription = SystemSubscription::expired()
                ->inGracePeriod()
                ->first();

            if ($subscription) {
                // In grace period - show warning but allow access
                View::share('subscription_warning', [
                    'type' => 'grace_period',
                    'message' => "Your subscription expired on {$subscription->subscription_end_date->format('d M, Y')}. " .
                                "Grace period ends on {$subscription->grace_period_ends_at->format('d M, Y')}. Please renew immediately.",
                    'days_remaining' => now()->diffInDays($subscription->grace_period_ends_at, false),
                ]);

                return $next($request);
            }

            // Check if there's any subscription at all (suspended, expired, etc.)
            $anySubscription = SystemSubscription::first();
            
            if ($anySubscription && $anySubscription->status === 'suspended') {
                // Suspended - block access
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'System suspended due to expired subscription. Please contact administrator.',
                        'status' => 'suspended',
                    ], 403);
                }

                return redirect()->route('subscription.suspended')
                    ->with('error', 'System access suspended. Please contact your administrator to renew the subscription.');
            }

            // No subscription exists at all - show locked page
            if (!$anySubscription) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'No active subscription. Please subscribe to continue.',
                        'status' => 'locked',
                    ], 403);
                }

                return redirect()->route('subscription.locked')
                    ->with('info', 'Please subscribe to access the system.');
            }
        }

        // Check if subscription is expiring soon (within 30 days)
        if ($subscription && $subscription->is_expiring_soon) {
            View::share('subscription_warning', [
                'type' => 'expiring_soon',
                'message' => "Your subscription expires in {$subscription->days_until_expiry} " .
                            ($subscription->days_until_expiry === 1 ? 'day' : 'days') . ". Please renew to avoid service interruption.",
                'days_remaining' => $subscription->days_until_expiry,
            ]);
        }

        return $next($request);
    }
}

