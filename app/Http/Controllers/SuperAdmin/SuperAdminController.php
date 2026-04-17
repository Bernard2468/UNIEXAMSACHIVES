<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSubscription;
use App\Models\PaymentTransaction;
use App\Models\SystemMaintenanceLog;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    /**
     * Handle super admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        if (\Auth::attempt($credentials)) {
            $user = \Auth::user();
            
            // Check if user is super admin
            if (!$user->isSuperAdmin()) {
                \Auth::logout();
                return back()->with('error', 'Access denied. Super Admin privileges required.');
            }
            
            $request->session()->regenerate();
            
            return redirect()->route('super-admin.dashboard')
                ->with('success', 'Welcome back, ' . $user->first_name . '!');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    /**
     * Display Super Admin Dashboard
     */
    public function dashboard()
    {
        // Overview statistics
        $stats = [
            'total_subscriptions' => SystemSubscription::count(),
            'active_subscriptions' => SystemSubscription::active()->count(),
            'expiring_soon' => SystemSubscription::expiringSoon(30)->count(),
            'expired_subscriptions' => SystemSubscription::expired()->count(),
            'suspended_subscriptions' => SystemSubscription::where('status', 'suspended')->count(),
            
            // Revenue statistics
            'total_revenue' => PaymentTransaction::completed()->sum('amount'),
            'monthly_revenue' => PaymentTransaction::completed()
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
            'yearly_revenue' => PaymentTransaction::completed()
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            
            // Transaction statistics
            'pending_payments' => PaymentTransaction::pending()->count(),
            'failed_payments' => PaymentTransaction::failed()->count(),
            'successful_payments' => PaymentTransaction::completed()->count(),
            
            // Maintenance statistics
            'upcoming_maintenance' => SystemMaintenanceLog::upcoming()->count(),
            'active_maintenance' => SystemMaintenanceLog::active()->count(),
            
            // User statistics
            'total_users' => User::count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'super_admin_users' => User::where('role', 'super_admin')->count(),
        ];

        // Recent activity
        $recentSubscriptions = SystemSubscription::with(['creator', 'transactions'])
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = PaymentTransaction::with(['subscription', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $upcomingMaintenance = SystemMaintenanceLog::upcoming(14)
            ->with('performer')
            ->get();

        // Expiring subscriptions alert
        $expiringSubscriptions = SystemSubscription::expiringSoon(14)
            ->with('creator')
            ->get();

        // Revenue chart data (last 12 months)
        $revenueChartData = $this->getRevenueChartData();

        // Subscription status chart data
        $subscriptionChartData = [
            'active' => $stats['active_subscriptions'],
            'expiring_soon' => $stats['expiring_soon'],
            'expired' => $stats['expired_subscriptions'],
            'suspended' => $stats['suspended_subscriptions'],
        ];

        return view('super-admin.dashboard', compact(
            'stats',
            'recentSubscriptions',
            'recentPayments',
            'upcomingMaintenance',
            'expiringSubscriptions',
            'revenueChartData',
            'subscriptionChartData'
        ));
    }

    /**
     * Display analytics page
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly

        $analytics = [
            'revenue' => $this->getRevenueAnalytics($period),
            'subscriptions' => $this->getSubscriptionAnalytics($period),
            'payments' => $this->getPaymentAnalytics($period),
            'users' => $this->getUserAnalytics($period),
        ];

        return view('super-admin.analytics', compact('analytics', 'period'));
    }

    /**
     * Manage user roles
     * is_admin: 0 = dashboard admin, 1 = regular user. role: super_admin = super admin.
     */
    public function manageRoles(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $users = User::with(['department', 'position', 'superAdminGrantor'])
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('is_admin', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $superAdmins = User::where('role', 'super_admin')->get();
        $admins = User::where('is_admin', 0)->where('role', '!=', 'super_admin')->get();
        $regularUsers = User::where('is_admin', 1)->get();

        return view('super-admin.roles.index', compact('users', 'superAdmins', 'admins', 'regularUsers'));
    }

    /**
     * Grant super admin access to a user
     */
    public function grantSuperAdmin(Request $request, int $userId)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $user = User::findOrFail($userId);

        if ($user->isSuperAdmin()) {
            return back()->with('warning', 'User is already a Super Administrator.');
        }

        $user->grantSuperAdminAccess(auth()->id());

        return back()->with('success', "Super Admin access granted to {$user->first_name} {$user->last_name}.");
    }

    /**
     * Revoke super admin access from a user
     */
    public function revokeSuperAdmin(Request $request, int $userId)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $user = User::findOrFail($userId);

        if (!$user->isSuperAdmin()) {
            return back()->with('warning', 'User is not a Super Administrator.');
        }

        // Prevent self-revocation
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot revoke your own Super Admin access.');
        }

        // Check if this is the last super admin
        $superAdminCount = User::where('role', 'super_admin')->count();
        if ($superAdminCount <= 1) {
            return back()->with('error', 'Cannot revoke access. At least one Super Admin must exist.');
        }

        $user->revokeSuperAdminAccess();

        return back()->with('success', "Super Admin access revoked from {$user->first_name} {$user->last_name}.");
    }

    /**
     * Grant admin access (make regular user a dashboard admin: is_admin = 0)
     */
    public function grantAdmin(Request $request, int $userId)
    {
        $request->validate(['confirm' => 'required|accepted']);

        $user = User::findOrFail($userId);

        if ($user->isSuperAdmin()) {
            return back()->with('warning', 'User is a Super Administrator. Use Revoke to change role.');
        }

        if ($user->is_admin == 0) {
            return back()->with('warning', 'User is already an Admin.');
        }

        $user->update(['is_admin' => 0]);

        return back()->with('success', "{$user->first_name} {$user->last_name} is now an Admin.");
    }

    /**
     * Revoke admin access (make admin a regular user: is_admin = 1)
     */
    public function revokeAdmin(Request $request, int $userId)
    {
        $request->validate(['confirm' => 'required|accepted']);

        $user = User::findOrFail($userId);

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot revoke Admin from a Super Administrator. Use Revoke to remove Super Admin first.');
        }

        if ($user->is_admin == 1) {
            return back()->with('warning', 'User is already a Regular User.');
        }

        $user->update(['is_admin' => 1]);

        return back()->with('success', "{$user->first_name} {$user->last_name} is now a Regular User.");
    }

    // ===== Private Helper Methods =====

    private function getRevenueChartData(): array
    {
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = PaymentTransaction::completed()
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');
            
            $data[] = [
                'month' => $month->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        }

        return $data;
    }

    private function getRevenueAnalytics(string $period): array
    {
        $query = PaymentTransaction::completed();

        switch ($period) {
            case 'daily':
                $days = 30;
                $data = [];
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $revenue = (clone $query)->whereDate('paid_at', $date->toDateString())->sum('amount');
                    $data[] = [
                        'label' => $date->format('M d'),
                        'value' => (float) $revenue,
                    ];
                }
                break;

            case 'weekly':
                $weeks = 12;
                $data = [];
                for ($i = $weeks - 1; $i >= 0; $i--) {
                    $startOfWeek = now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = now()->subWeeks($i)->endOfWeek();
                    $revenue = (clone $query)
                        ->whereBetween('paid_at', [$startOfWeek, $endOfWeek])
                        ->sum('amount');
                    $data[] = [
                        'label' => 'Week ' . $startOfWeek->weekOfYear,
                        'value' => (float) $revenue,
                    ];
                }
                break;

            case 'yearly':
                $years = 5;
                $data = [];
                for ($i = $years - 1; $i >= 0; $i--) {
                    $year = now()->subYears($i)->year;
                    $revenue = (clone $query)->whereYear('paid_at', $year)->sum('amount');
                    $data[] = [
                        'label' => $year,
                        'value' => (float) $revenue,
                    ];
                }
                break;

            default: // monthly
                $data = $this->getRevenueChartData();
                break;
        }

        return [
            'chart_data' => $data,
            'total' => (float) $query->sum('amount'),
            'average' => (float) $query->avg('amount'),
        ];
    }

    private function getSubscriptionAnalytics(string $period): array
    {
        return [
            'by_status' => SystemSubscription::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray(),
            
            'by_plan' => SystemSubscription::select('subscription_plan', DB::raw('count(*) as count'))
                ->groupBy('subscription_plan')
                ->get()
                ->pluck('count', 'subscription_plan')
                ->toArray(),
            
            'renewal_rate' => $this->calculateRenewalRate(),
            'churn_rate' => $this->calculateChurnRate(),
        ];
    }

    private function getPaymentAnalytics(string $period): array
    {
        return [
            'by_status' => PaymentTransaction::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray(),
            
            'by_method' => PaymentTransaction::select('payment_method', DB::raw('count(*) as count'))
                ->whereNotNull('payment_method')
                ->groupBy('payment_method')
                ->get()
                ->pluck('count', 'payment_method')
                ->toArray(),
            
            'success_rate' => $this->calculatePaymentSuccessRate(),
            'average_transaction_value' => (float) PaymentTransaction::completed()->avg('amount'),
        ];
    }

    private function getUserAnalytics(string $period): array
    {
        return [
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role')
                ->toArray(),
            
            'approved_users' => User::where('is_approve', true)->count(),
            'pending_approval' => User::where('is_approve', false)->count(),
            
            'new_users_this_month' => User::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];
    }

    private function calculateRenewalRate(): float
    {
        $totalRenewals = PaymentTransaction::where('transaction_type', 'subscription_renewal')
            ->completed()
            ->count();

        $totalSubscriptions = SystemSubscription::count();

        return $totalSubscriptions > 0 ? round(($totalRenewals / $totalSubscriptions) * 100, 2) : 0;
    }

    private function calculateChurnRate(): float
    {
        $suspendedCount = SystemSubscription::where('status', 'suspended')->count();
        $totalCount = SystemSubscription::count();

        return $totalCount > 0 ? round(($suspendedCount / $totalCount) * 100, 2) : 0;
    }

    private function calculatePaymentSuccessRate(): float
    {
        $total = PaymentTransaction::whereIn('status', ['completed', 'failed'])->count();
        $successful = PaymentTransaction::completed()->count();

        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }
}

